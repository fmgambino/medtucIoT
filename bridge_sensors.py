import paho.mqtt.client as mqtt
import mysql.connector as mysql
import json

# Conexión a MySQL
db = mysql.connect(
    host="localhost",
    user="root",
    password="",  # cambia si tenés contraseña
    database="medtuciot"
)
cursor = db.cursor()

# Obtener device_id numérico usando esp32_id (ej: ESPA7B0)
def get_device_id(esp32_id):
    cursor.execute("SELECT id FROM devices WHERE esp32_id = %s", (esp32_id,))
    result = cursor.fetchone()
    return result[0] if result else None

# Callback al recibir mensaje MQTT
def on_message(client, userdata, msg):
    try:
        parts = msg.topic.split('/')
        if len(parts) != 3:
            print(f"⚠️ Tópico mal formado: {msg.topic}")
            return

        _, device_mqtt, variable = parts
        device_id = get_device_id(device_mqtt)

        if not device_id:
            print(f"❌ No existe device_id para esp32_id '{device_mqtt}'")
            return

        payload = msg.payload.decode('utf-8')
        is_json = payload.startswith('{') and payload.endswith('}')
        data = json.loads(payload) if is_json else payload

        if isinstance(data, dict):
            if variable == 'tempHum':
                if 'temperature' in data and 'humidity' in data:
                    # Guardar JSON compuesto
                    cursor.execute("""
                        INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                        VALUES (%s, %s, %s, %s)
                    """, (device_id, 'tempHum', json.dumps(data), ''))

                    # Insertar también temperature
                    cursor.execute("""
                        INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                        VALUES (%s, %s, %s, %s)
                    """, (device_id, 'temperature', float(data['temperature']), '°C'))

                    # Insertar también humidity
                    cursor.execute("""
                        INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                        VALUES (%s, %s, %s, %s)
                    """, (device_id, 'humidity', float(data['humidity']), '%'))

                else:
                    print(f"⚠️ Campos faltantes en tempHum: {data}")
                    return

            elif variable == 'mq135':
                if any(k in data for k in ['co2', 'methane', 'butane', 'propane']):
                    cursor.execute("""
                        INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                        VALUES (%s, %s, %s, %s)
                    """, (device_id, 'mq135', json.dumps(data), ''))
                else:
                    print(f"⚠️ Campos faltantes en mq135: {data}")
                    return
            else:
                print(f"⚠️ JSON recibido inesperado en variable: {variable}")
                return

        else:
            # Valor simple
            try:
                value = float(data)
            except ValueError:
                print(f"⚠️ Valor no numérico: {data}")
                return

            unit_map_simple = {
                'soilHum': '%',
                'ph': '',
                'ec': 'μS/cm',
                'h2o': '%',
                'nafta': '%',
                'aceite': '%',
                'sLDR': ''
            }
            unit = unit_map_simple.get(variable, '')
            cursor.execute("""
                INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                VALUES (%s, %s, %s, %s)
            """, (device_id, variable, value, unit))

        db.commit()
        print(f"✅ Insertado: {msg.topic} → {payload}")

    except Exception as e:
        print(f"❌ Error al procesar {msg.topic} → {msg.payload}: {e}")

# Conexión MQTT
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = lambda c, u, f, rc, properties=None: print("📡 MQTT conectado") or c.subscribe("medtucIoT/+/+")
client.on_message = on_message

print("🚀 Iniciando cliente MQTT...")
client.connect("broker.emqx.io", 1883, 60)
client.loop_forever()
