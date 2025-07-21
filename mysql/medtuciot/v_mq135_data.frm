TYPE=VIEW
query=select `medtuciot`.`sensor_data`.`id` AS `id`,`medtuciot`.`sensor_data`.`device_id` AS `device_id`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.co2\')) AS `co2`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.methane\')) AS `methane`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.butane\')) AS `butane`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.propane\')) AS `propane`,`medtuciot`.`sensor_data`.`timestamp` AS `timestamp` from `medtuciot`.`sensor_data` where `medtuciot`.`sensor_data`.`sensor_type` = \'mq135\'
md5=a62c3bbb04938b07e7d547c149c5f8f3
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001753130525170398
create-version=2
source=SELECT \n  id,\n  device_id,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.co2\')) AS co2,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.methane\')) AS methane,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.butane\')) AS butane,\n  JSON_UNQUOTE(JSON_EXTRACT(value, \'$.propane\')) AS propane,\n  timestamp\nFROM sensor_data\nWHERE sensor_type = \'mq135\'
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `medtuciot`.`sensor_data`.`id` AS `id`,`medtuciot`.`sensor_data`.`device_id` AS `device_id`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.co2\')) AS `co2`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.methane\')) AS `methane`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.butane\')) AS `butane`,json_unquote(json_extract(`medtuciot`.`sensor_data`.`value`,\'$.propane\')) AS `propane`,`medtuciot`.`sensor_data`.`timestamp` AS `timestamp` from `medtuciot`.`sensor_data` where `medtuciot`.`sensor_data`.`sensor_type` = \'mq135\'
mariadb-version=100432
