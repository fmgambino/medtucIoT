const mqtt  = require('mqtt');
const mysql = require('mysql2/promise');

(async () => {
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    database: 'medtuciot'
  });

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
    console.log('✅ MQTT conectado y suscrito a medtucIoT/+/+');
  });

  client.on('message', async (topic, msg) => {
    const [, deviceId, variable] = topic.split('/');
    const payload = msg.toString();

    try {
      if (payload.startsWith('{') && payload.endsWith('}')) {
        // JSON compuesto
        let parsed;
        try {
          parsed = JSON.parse(payload);
          // Validar campos
          if (variable === 'tempHum' && (parsed.temp == null || parsed.hum == null)) {
            throw new Error('Campos faltantes en tempHum');
          }
          if (variable === 'mq135' && (
            parsed.co2 == null && parsed.methane == null &&
            parsed.butane == null && parsed.propane == null
          )) {
            throw new Error('Campos faltantes en mq135');
          }

          await db.execute(
            'INSERT INTO sensor_data (device_id, sensor_type, value, unit, timestamp) VALUES (?, ?, ?, ?, NOW())',
            [deviceId, variable, JSON.stringify(parsed), '',]
          );
          console.log(`✅ Insertado JSON: ${topic} → ${payload}`);
        } catch (e) {
          console.error(`❌ JSON inválido en ${topic}:`, payload, '→', e.message);
        }

      } else {
        // Sensor simple
        const val = parseFloat(payload);
        if (isNaN(val)) {
          console.warn(`⚠️ Valor no numérico recibido para ${topic}: ${payload}`);
          return;
        }
        const unit = unitMapSimple[variable] ?? '';
        await db.execute(
          'INSERT INTO sensor_data (device_id, sensor_type, value, unit, timestamp) VALUES (?, ?, ?, ?, NOW())',
          [deviceId, variable, val, unit]
        );
        console.log(`✅ Insertado simple: ${topic} → ${val}`);
      }

    } catch (err) {
      console.error('❌ Error general MQTT → DB:', err);
    }
  });
})();
