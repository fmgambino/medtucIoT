# bridge_sensors.py
import json
import mysql.connector
import paho.mqtt.client as mqtt

# --- 1) Conexión a MySQL (XAMPP) ---
db = mysql.connector.connect(
  host="127.0.0.1",
  user="root",
  password="",           # tu password XAMPP
  database="medtuciot"
)
cursor = db.cursor()

# --- 2) Callbacks MQTT ---
def on_connect(client, userdata, flags, rc):
    print("MQTT conectado, suscribiendo a medtucIoT/+/+ …")
    client.subscribe("medtucIoT/+/+")

def on_message(client, userdata, msg):
    topic = msg.topic             # e.g. medtucIoT/101/tempHum
    payload = msg.payload.decode()
    parts = topic.split('/')
    device_id    = parts[1]
    sensor_type  = parts[2]       # tempHum, co2, soilHum, etc.

    try:
        if payload.startswith('{'):
            # JSON: { "temp":23.4, "hum":56.7 }
            data = json.loads(payload)
            for key, val in data.items():
                cursor.execute(
                  "INSERT INTO sensor_data (device_id,sensor_type,value) VALUES (%s,%s,%s)",
                  (device_id, key, val)
                )
        else:
            # valor único
            val = float(payload)
            cursor.execute(
              "INSERT INTO sensor_data (device_id,sensor_type,value) VALUES (%s,%s,%s)",
              (device_id, sensor_type, val)
            )
        db.commit()
        print(f"{topic} → {payload} guardado en DB")
    except Exception as e:
        print("Error al insertar:", e)

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

# --- 3) Conectar al broker EMQX ---
client.connect("broker.emqx.io", 1883, 60)
client.loop_forever()
