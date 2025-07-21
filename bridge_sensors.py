import paho.mqtt.client as mqtt
import mysql.connector as mysql
import json

# Conexión a MySQL
db = mysql.connect(
    host="localhost",
    user="root",
    password="",  # ajusta si tienes contraseña
    database="medtuciot"
)
cursor = db.cursor()

# Obtener device_id numérico usando esp32_id
def get_device_id(esp32_id):
    cursor.execute("SELECT id FROM devices WHERE esp32_id = %s", (esp32_id,))
    result = cursor.fetchone()
    return result[0] if result else None

# Callback al recibir mensaje MQTT
def on_message(client, userdata, msg):
    try:
        _, device_mqtt, variable = msg.topic.split('/')
        device_id = get_device_id(device_mqtt)

        if not device_id:
            print(f"No existe device_id para esp32_id '{device_mqtt}'")
            return

        payload = msg.payload.decode('utf-8')
        data = json.loads(payload) if payload.startswith('{') else payload

        if isinstance(data, dict):
            unit_map = {'temp': '°C', 'hum': '%', 'co2': 'ppm', 'methane': 'ppm',
                        'butane': 'ppm', 'propane': 'ppm'}
            for key, value in data.items():
                unit = unit_map.get(key, '')
                cursor.execute("""
                    INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                    VALUES (%s, %s, %s, %s)
                """, (device_id, key, float(value), unit))
        else:
            unit_map_simple = {
                'soilHum': '%', 'ph': '', 'ec': 'μS/cm', 'h2o': '%', 'nafta': '%', 'aceite': '%'
            }
            unit = unit_map_simple.get(variable, '')
            cursor.execute("""
                INSERT INTO sensor_data (device_id, sensor_type, value, unit)
                VALUES (%s, %s, %s, %s)
            """, (device_id, variable, float(data), unit))

        db.commit()
        print(f"Datos insertados correctamente: {msg.topic} → {payload}")

    except Exception as e:
        print(f"Error al insertar {msg.topic} → {msg.payload}: {e}")

# Conexión MQTT
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = lambda c, u, f, rc, properties=None: print("MQTT conectado") or c.subscribe("medtucIoT/+/+")
client.on_message = on_message

print("Iniciando cliente MQTT...")
client.connect("broker.emqx.io", 1883, 60)
client.loop_forever()
