#include <Arduino.h>
#include <WiFi.h>
#include <WiFiClient.h>
#include <WiFiManager.h>        // https://github.com/tzapu/WiFiManager
#include <PubSubClient.h>       // https://github.com/knolleary/pubsubclient
#include <ArduinoJson.h>        // https://github.com/bblanchon/ArduinoJson

// --- CONFIGURACIÓN MQTT ---
static const char* MQTT_HOST    = "broker.emqx.io";
static const uint16_t MQTT_PORT = 1883;

// --- IDENTIFICADOR DINÁMICO DEL DISPOSITIVO ---
char DEVICE_ID[8];   // cabrá "ESP" + 4 hex + '\0'

// --- TÓPICO BASE ---
static const char* TOPIC_BASE = "medtucIoT/";

// --- CLIENTES MQTT/WIFI ---
WiFiClient   wifiClient;
PubSubClient mqtt(wifiClient);

// --- ESTRUCTURA DE SENSORES ---
struct Sensor {
  const char* variable;   // sensor_type en la BD
  const char* suffix;     // sufijo tras DEVICE_ID/
  float       min, max;
  uint8_t     dec;
};

// Lista de sensores agrupados
Sensor sensors[] = {
  { "tempHum", "tempHum", 15.0, 35.0, 1 },    // JSON { temp, hum }
  { "mq135",   "mq135",   0,    0,    0 },    // JSON { co2, methane, butane, propane }
  { "soilHum", "soilHum", 10.0, 90.0, 1 },
  { "ph",      "ph",      4.0,   9.0,  2 },
  { "ec",      "ec",      200,  2000, 0 },
  { "h2o",     "h2o",     0,    100, 0 },
  { "nafta",   "nafta",   0,    100, 0 },
  { "aceite",  "aceite",  0,    100, 0 }
};
constexpr size_t NUM_SENSORS = sizeof(sensors) / sizeof(sensors[0]);

// --- ESTRUCTURA DE ACTUADORES ---
struct Actuator {
  const char* id;
  const char* suffix;
};
Actuator actuators[] = {
  { "A1", "A1" },
  { "A2", "A2" },
  { "A3", "A3" },
  { "A4", "A4" }
};
constexpr size_t NUM_ACT = sizeof(actuators) / sizeof(actuators[0]);

// --- FUNCIÓN ALEATORIA CON DECIMALES ---
float rndf(float lo, float hi, uint8_t dec) {
  float r = random(0, 10000) / 10000.0f;
  float v = lo + r * (hi - lo);
  float f = powf(10, dec);
  return roundf(v * f) / f;
}

// --- RECONEXIÓN MQTT ---
void mqttReconnect() {
  while (!mqtt.connected()) {
    Serial.print("Conectando MQTT… ");
    if (mqtt.connect(DEVICE_ID)) {
      Serial.println("¡Conectado!");
    } else {
      Serial.printf("Error rc=%d, reintentando en 5s\n", mqtt.state());
      delay(5000);
    }
  }
}

// --- PUBLICAR TEMP+HUM EN JSON ---
void publishTempHum() {
  StaticJsonDocument<64> doc;

  float tempVal = rndf(20.0, 35.0, 1);
  float humVal  = rndf(40.0, 80.0, 1);

  doc["temperature"] = tempVal;
  doc["humidity"]    = humVal;

  char buf[64];
  size_t n = serializeJson(doc, buf);
  char topic[64];
  snprintf(topic, sizeof(topic), "%s%s/tempHum", TOPIC_BASE, DEVICE_ID);
  mqtt.publish(topic, buf, n);
  Serial.printf("📡 tempHum → %s\n", buf);
}


// --- PUBLICAR MQ135 (CO₂, METANO, BUTANO, PROPANO) EN JSON ---
void publishMQ135() {
  StaticJsonDocument<128> doc;
  doc["co2"]     = rndf(300, 2000, 0);
  doc["methane"] = rndf(0,   200,  0);
  doc["butane"]  = rndf(0,   200,  0);
  doc["propane"] = rndf(0,   200,  0);
  char buf[128];
  size_t n = serializeJson(doc, buf);
  char topic[64];
  snprintf(topic, sizeof(topic), "%s%s/mq135", TOPIC_BASE, DEVICE_ID);
  mqtt.publish(topic, buf, n);
  Serial.printf("📡 mq135 → %s\n", buf);
}

// --- PUBLICAR VALOR ÚNICO ---
void publishSensorValue(const Sensor& s) {
  float v = rndf(s.min, s.max, s.dec);
  char payload[16];
  dtostrf(v, 0, s.dec, payload);
  char topic[64];
  snprintf(topic, sizeof(topic), "%s%s/%s", TOPIC_BASE, DEVICE_ID, s.suffix);
  mqtt.publish(topic, payload);
  Serial.printf("📡 %s → %s\n", s.variable, payload);
}

// --- PUBLICAR ACTUADOR ON/OFF ---
void publishActuator(const Actuator& a) {
  const char* st = random(0,2) ? "ON" : "OFF";
  char topic[64];
  snprintf(topic, sizeof(topic), "%s%s/act/%s", TOPIC_BASE, DEVICE_ID, a.suffix);
  mqtt.publish(topic, st);
  Serial.printf("🔌 Act %s → %s\n", a.id, st);
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  // Generar DEVICE_ID dinámico: "ESP" + últimos 4 hexitos de la MAC
  uint64_t mac = ESP.getEfuseMac();
  uint16_t last4 = mac & 0xFFFF;
  snprintf(DEVICE_ID, sizeof(DEVICE_ID), "ESP%04X", last4);
  Serial.printf("Mi DEVICE_ID es %s\n", DEVICE_ID);

  randomSeed(analogRead(0));

  // WiFiManager captive portal
  WiFiManager wm;
  if (!wm.autoConnect("ESP32-Setup")) {
    Serial.println("Fallo portal WiFi");
    ESP.restart();
  }
  Serial.printf("WiFi OK, IP %s\n", WiFi.localIP().toString().c_str());

  // Configurar MQTT
  mqtt.setServer(MQTT_HOST, MQTT_PORT);
}

void loop() {
  if (!mqtt.connected()) {
    mqttReconnect();
  }
  mqtt.loop();

  static unsigned long lastSens = 0;
  // 3 600 000 ms = 60 min
  if (millis() - lastSens >= 15000) {
    lastSens = millis();
    publishTempHum();
    publishMQ135();
    for (size_t i = 2; i < NUM_SENSORS; ++i) {
      publishSensorValue(sensors[i]);
    }
  }

  static unsigned long lastAct = 0;
  if (millis() - lastAct >= 100000) {
    lastAct = millis();
    for (size_t i = 0; i < NUM_ACT; ++i) {
      publishActuator(actuators[i]);
    }
  }
}
