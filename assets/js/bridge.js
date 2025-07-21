// bridge.js corregido
const mqtt  = require('mqtt');
const mysql = require('mysql2/promise');

(async () => {
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    database: 'medtuciot'
  });

  // Mapas de unidades para cada subtipo de datos JSON y para valores simples
  const unitMapJson = {
    temp: '°C',
    hum: '%',
    co2: 'ppm',
    methane: 'ppm',
    butane: 'ppm',
    propane: 'ppm'
  };

  const unitMapSimple = {
    soilHum: '%',
    ph: '',
    ec: 'μS/cm',
    h2o: '%',
    nafta: '%',
    aceite: '%',
    sLDR: ''
  };

  const client = mqtt.connect('mqtt://broker.emqx.io:1883');

  client.on('connect', () => {
    client.subscribe('medtucIoT/+/+');
    console.log('MQTT conectado y suscrito a medtucIoT/+/+');
  });

  client.on('message', async (topic, msg) => {
    try {
      const [, deviceId, variable] = topic.split('/');
      const payload = msg.toString();

      // Detectar si es JSON compuesto o valor simple
      if (payload.startsWith('{') && payload.endsWith('}')) {
        // JSON compuesto (tempHum, mq135)
        const data = JSON.parse(payload);
        for (let key in data) {
          const val = parseFloat(data[key]);
          const unit = unitMapJson[key] ?? '';
          await db.execute(
            'INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (?, ?, ?, ?)',
            [deviceId, key, val, unit]
          );
        }
      } else {
        // Valor simple (soilHum, ph, etc.)
        const val = parseFloat(payload);
        const unit = unitMapSimple[variable] ?? '';
        await db.execute(
          'INSERT INTO sensor_data (device_id, sensor_type, value, unit) VALUES (?, ?, ?, ?)',
          [deviceId, variable, val, unit]
        );
      }

      console.log(`Dato insertado: ${topic} → ${payload}`);

    } catch (err) {
      console.error('MQTT → DB error:', err);
    }
  });
})();
