TYPE=VIEW
query=select `medtuciot`.`sensor_data`.`id` AS `id`,`medtuciot`.`sensor_data`.`device_id` AS `device_id`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.temperature\')) AS `temperature`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.humidity\')) AS `humidity`,`medtuciot`.`sensor_data`.`timestamp` AS `timestamp` from `medtuciot`.`sensor_data` where `medtuciot`.`sensor_data`.`sensor_type` = \'tempHum\'
md5=7791ebfdd0515df0a2e7d9f142524a3d
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001753130525156756
create-version=2
source=SELECT \n  id,\n  device_id,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.temperature\')) AS temperature,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.humidity\')) AS humidity,\n  timestamp\nFROM sensor_data\nWHERE sensor_type = \'tempHum\'
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `medtuciot`.`sensor_data`.`id` AS `id`,`medtuciot`.`sensor_data`.`device_id` AS `device_id`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.temperature\')) AS `temperature`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.humidity\')) AS `humidity`,`medtuciot`.`sensor_data`.`timestamp` AS `timestamp` from `medtuciot`.`sensor_data` where `medtuciot`.`sensor_data`.`sensor_type` = \'tempHum\'
mariadb-version=100432
