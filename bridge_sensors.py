import paho.mqtt.client as mqtt
import mysql.connector as mysql
import json
import sys

# --- CONFIGURACIÓN BASES DE DATOS ---
LOCAL_DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",  # XAMPP: colocar contraseña si corresponde
    "database": "medtuciot"
}

REMOTE_DB_CONFIG = {
    "host": "srv1543.hstgr.io",  # ← Hostinger te lo dio
    "user": "u197809344_fmgiot",
    "password": "Jamboree0381$$",
    "database": "u197809344_medtuciot"
}

# --- PROBAR CONEXIONES ---
try:
    local_db = mysql.connect(**LOCAL_DB_CONFIG)
    local_cursor = local_db.cursor()
    print("✅ Conexión OK a base de datos LOCAL (XAMPP)")
except mysql.Error as err:
    print(f"❌ Error en conexión local: {err}")
    sys.exit(1)

try:
    remote_db = mysql.connect(**REMOTE_DB_CONFIG)
    remote_cursor = remote_db.cursor()
    print("✅ Conexión OK a base de datos REMOTA (Hostinger)")
except mysql.Error as err:
    print(f"❌ Error en conexión remota: {err}")
    sys.exit(1)

# --- OBTENER ID DISPOSITIVO ---
def get_device_id(cursor, esp32_id):
    cursor.execute("SELECT id FROM devices WHERE esp32_id = %s", (esp32_id,))
    result = cursor.fetchone()
    return result[0] if result else None

# --- EJECUTAR EN AMBAS BASES ---
def execute_dual(query, values):
    for cursor, db in [(local_cursor, local_db), (remote_cursor, remote_db)]:
        try:
            cursor.execute(query, values)
            db.commit()
        except mysql.Error as e:
            print(f"❌ Error al insertar en DB: {e}")

# --- CALLBACK MQTT ---
def on_message(client, userdata, msg):
    try:
        parts = msg.topic.split('/')
        if len(parts) != 3:
            print(f"⚠️ Tópico mal formado: {msg.topic}")
            return

        _, device_mqtt, variable = parts
        device_id = get_device_id(local_cursor, device_mqtt)

        if not device_id:
            print(f"❌ Dispositivo desconocido: {device_mqtt}")
            return

        payload = msg.payload.decode('utf-8')
        is_json = payload.startswith('{') and payload.endswith('}')
        data = json.loads(payload) if is_json else payload

        if isinstance(data, dict):
            if variable == 'tempHum':
                if 'temperature' in data and 'humidity' in data:
                    # JSON combinado
                    execute_dual(
                        "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                        (device_id, 'tempHum', json.dumps(data), '')
                    )
                    # Individuales
                    execute_dual(
                        "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                        (device_id, 'temperature', float(data['temperature']), '°C')
                    )
                    execute_dual(
                        "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                        (device_id, 'humidity', float(data['humidity']), '%')
                    )
                else:
                    print(f"⚠️ Campos faltantes en tempHum: {data}")
                    return

            elif variable == 'mq135':
                if any(k in data for k in ['co2', 'methane', 'butane', 'propane']):
                    execute_dual(
                        "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                        (device_id, 'mq135', json.dumps(data), '')
                    )
                else:
                    print(f"⚠️ Campos faltantes en mq135: {data}")
                    return

            else:
                print(f"⚠️ JSON inesperado para variable: {variable}")
                return

        else:
            try:
                value = float(data)
            except ValueError:
                print(f"⚠️ Valor no numérico: {data}")
                return

            unit_map = {
                'soilHum': '%',
                'ph': '',
                'ec': 'μS/cm',
                'h2o': '%',
                'nafta': '%',
                'aceite': '%',
                'sLDR': ''
            }
            unit = unit_map.get(variable, '')
            execute_dual(
                "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                (device_id, variable, value, unit)
            )

        print(f"✅ Insertado: {msg.topic} → {payload}")

    except Exception as e:
        print(f"❌ Error procesando {msg.topic}: {e}")

# --- MQTT SETUP ---
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = lambda c, u, f, rc, properties=None: print("📡 MQTT conectado") or c.subscribe("medtucIoT/+/+")
client.on_message = on_message

print("🚀 Iniciando cliente MQTT...")
client.connect("broker.emqx.io", 1883, 60)
client.loop_forever()
