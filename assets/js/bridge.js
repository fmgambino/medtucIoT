// bridge.js
const mqtt  = require('mqtt');
const mysql = require('mysql2/promise');

(async () => {
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    database: 'medtuciot'
  });

  // Mapea cada variable a su unidad
  const unitMap = {
    tempHum: '%',    // humedad; temperatura asumimos °C en front
    co2:     'ppm',
    soilHum: '%',
    ph:      '',
    ec:      'μS/cm',
    h2o:     '%',
    nafta:   '%',
    aceite:  '%',
    sLDR:    ''      // si no hay unidad
  };

  const client = mqtt.connect('mqtt://broker.emqx.io:1883');

  client.on('connect', () => {
    client.subscribe('medtucIoT/+/+');
  });

  client.on('message', async (topic, msg) => {
    try {
      // topic = medtucIoT/{deviceId}/{variable}
      const [, deviceId, variable] = topic.split('/');
      const data = JSON.parse(msg.toString());
      const unit = unitMap[variable] ?? '';

      // Inserta cada par clave→valor como fila en sensor_data
      for (let key in data) {
        const v = parseFloat(data[key]);
        await db.execute(
          'INSERT INTO sensor_data (device_id,sensor_type,value,unit) VALUES (?,?,?,?)',
          [deviceId, variable, v, unit]
        );
      }
    } catch (err) {
      console.error('MQTT → DB error:', err);
    }
  });
})();
