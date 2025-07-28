# mqtt_publish.py
import paho.mqtt.publish as publish
import sys
import json

# ---------- Parámetros esperados ----------
# 1: ESP32 ID (ej: ESP12345)
# 2: Comando (ej: restart, relay_on, relay_off)
# 3: Parámetro opcional extra

if len(sys.argv) < 3:
    print("Uso: python mqtt_publish.py ESPID comando [param]")
    sys.exit(1)

espid = sys.argv[1]
command = sys.argv[2]
param = sys.argv[3] if len(sys.argv) > 3 else None

# ---------- Payload ----------
payload = {"action": command}
if param:
    payload["value"] = param

topic = f"medtucIoT/{espid}/command"
message = json.dumps(payload)

try:
    publish.single(
        topic,
        payload=message,
        hostname="broker.emqx.io",
        port=1883,
        qos=1,
        retain=False
    )
    print(f"✅ Enviado a {topic}: {message}")

except Exception as e:
    print(f"❌ Error publicando: {e}")
    sys.exit(1)
