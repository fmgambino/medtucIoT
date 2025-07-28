import paho.mqtt.client as mqtt
import mysql.connector as mysql
import json
import sys
import os

# ---------------- CONFIGURACIÓN BASE DE DATOS ---------------- #
LOCAL_DB = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "medtuciot"
}
REMOTE_DB = {
    "host": "srv1543.hstgr.io",
    "user": "u197809344_fmgiot",
    "password": "Jamboree0381$$",
    "database": "u197809344_medtuciot"
}

# ---------------- CONEXIÓN A BASES ---------------- #
def connect_db(config):
    try:
        conn = mysql.connect(**config)
        print(f"✅ Conexión OK a DB: {config['host']}")
        return conn, conn.cursor()
    except mysql.Error as err:
        print(f"❌ Error conexión DB {config['host']}: {err}")
        sys.exit(1)

local_db, local_cursor = connect_db(LOCAL_DB)
remote_db, remote_cursor = connect_db(REMOTE_DB)

def execute_dual(query, params):
    for db, cur in [(local_db, local_cursor), (remote_db, remote_cursor)]:
        try:
            cur.execute(query, params)
            db.commit()
        except mysql.Error as e:
            print(f"❌ DB error: {e}")

# ---------------- OBTENER ID DISPOSITIVO ---------------- #
def get_device_id(cursor, espid):
    cursor.execute("SELECT id FROM devices WHERE espid = %s", (espid,))
    res = cursor.fetchone()
    return res[0] if res else None

# ---------------- CALLBACK MQTT ---------------- #
def on_message(client, userdata, msg):
    topic_parts = msg.topic.split('/')
    if len(topic_parts) != 3:
        print(f"⚠️ Tópico mal formado: {msg.topic}")
        return

    _, espid, var = topic_parts
    payload = msg.payload.decode('utf-8')

    device_id = get_device_id(local_cursor, espid)
    if not device_id:
        print(f"❌ Dispositivo desconocido: {espid}")
        return

    # ----- COMANDO MQTT: restart / relay_on / relay_off -----
    if var == 'command':
        try:
            data = json.loads(payload)
            if not isinstance(data, dict) or 'action' not in data:
                print("⚠️ Comando malformado")
                return
            action = data['action']
            print(f"📥 Comando recibido para {espid}: {action}")

            execute_dual(
                "INSERT INTO mqtt_commands (device_id, command, raw) VALUES (%s, %s, %s)",
                (device_id, action, payload)
            )
            return

        except Exception as e:
            print(f"❌ Error comando: {e}")
            return

    # ----- SENSOR DATA -----
    try:
        data = json.loads(payload) if payload.startswith('{') else payload

        if isinstance(data, dict):
            if var == 'tempHum':
                for k in ['temperature', 'humidity']:
                    if k in data:
                        execute_dual(
                            "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                            (device_id, k, float(data[k]), '°C' if k == 'temperature' else '%')
                        )
                return

            elif var == 'mq135':
                execute_dual(
                    "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                    (device_id, 'mq135', json.dumps(data), '')
                )
                return

            # Otros JSON sensores
            execute_dual(
                "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                (device_id, var, json.dumps(data), '')
            )

        else:
            # Simple float
            value = float(data)
            unit_map = {
                'soilHum': '%', 'ph': '', 'ec': 'μS/cm', 'h2o': '%', 'nafta': '%',
                'aceite': '%', 'ldr': '', 'temp': '°C'
            }
            execute_dual(
                "INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (%s, %s, %s, %s)",
                (device_id, var, value, unit_map.get(var, ''))
            )

        print(f"✅ Insertado: {msg.topic} → {payload}")

    except Exception as e:
        print(f"❌ Error procesando mensaje: {e}")

# ---------------- CLIENTE MQTT ---------------- #
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = lambda c, u, f, rc, props=None: (
    print("📡 MQTT conectado"),
    c.subscribe("medtucIoT/+/+")
)
client.on_message = on_message

print("🚀 Iniciando bridge MQTT unificado...")
client.connect("broker.emqx.io", 1883, 60)
client.loop_forever()
