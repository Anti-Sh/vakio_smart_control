<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='vakio_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

  // Создаём список с наименованием типов устройств
  $out['VAKIO_TYPES'] = array(
    0 => array(
      "TITLE"=>"Atmosphere",
      "SELECTED"=>false,
      "ID"=>0
    ),
    1 => array(
      "TITLE"=>"Base Smart",
      "SELECTED"=>false,
      "ID"=>1
    ),
    2 => array(
      "TITLE"=>"Kiv Pro/New",
      "SELECTED"=>false,
      "ID"=>2
    ),
    3 => array(
      "TITLE"=>"Openair",
      "SELECTED"=>false,
      "ID"=>3
    ),
  );
if ($this->tab=='') {
  if ($this->mode=='update') {
    $ok=1;
    $rec['TITLE']=gr('title');
    $rec['VAKIO_DEVICE_TYPE']=gr('vakio_device_type');
    $rec['VAKIO_DEVICE_MQTT_TOPIC']=gr('vakio_device_mqtt_topic');

    if ($rec['TITLE']=='') {
      $out['ERR_TITLE']=1;
      $ok=0;
    }
    if ($rec['VAKIO_DEVICE_TYPE'] > 3) {
      $out['ERR_VAKIO_DEVICE_TYPE']=1;
      $ok=0;
    }
    if ($rec['VAKIO_DEVICE_MQTT_TOPIC']=='') {
      $out['ERR_VAKIO_DEVICE_MQTT_TOPIC']=1;
      $ok=0;
    }

    $rec['VAKIO_DEVICE_STATE']= "{}";
    //UPDATING RECORD
    if ($ok) {
      if (isset($rec['ID'])) {
        SQLUpdate($table_name, $rec); // update
      } else {
        $new_rec=1;
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
		if($rec['VAKIO_DEVICE_TYPE'] == 0){
			$data = [['temp','Температура'],
			['co2','CO2'],
			['hud','Влажность']];
		} else if($rec['VAKIO_DEVICE_TYPE'] == 1){
			$data = [['state','Состояние'],
			['workmode','Режим'],
			['speed','Скорость'],
			['mode','Управление']];
		} else if($rec['VAKIO_DEVICE_TYPE'] == 2){
			$data = [['state','Состояние'],
			['gate','Заслонка']];
		} else if($rec['VAKIO_DEVICE_TYPE'] == 3){
			$data = [['state','Состояние'],
			['gate','Заслонка'],
			['speed','Скорость'],
			['workmode','Режим'],
			['temp','Температура'],
			['hud','Влажность']];
		}
		$prop['DEVICE_ID'] = $rec['ID'];
		for ($i=0; $i<count($data); $i++){
		  $prop['TITLE'] = $data[$i][0];
		  $prop['NAME'] = $data[$i][1];
		  $prop['UPDATED'] = date('Y-m-d H:i:s');
		  SQLInsert('vakio_info', $prop);
	  }
      }
      $out['OK']=1;
	  setGlobal('cycle_vakioControl','restart');
    } else {
      $out['ERR']=1;
    }
  }
	$out["VAKIO_TYPES"][$rec["VAKIO_DEVICE_TYPE"]]["SELECTED"] = true;
}
  
    // Вкладка свойств
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   $sortby = gr('sortby');
   if ($sortby) $sort = $sortby;
   else $sort = "ID";
   $properties=SQLSelect("SELECT * FROM vakio_info WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ".$sort);
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
	  $old_title=$properties[$i]['TITLE'];
	  $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      global ${'linked_method'.$properties[$i]['ID']};
      $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['ID']});
	  // Если юзер удалил привязанные свойство и метод, но забыл про объект, то очищаем его.
      if ($properties[$i]['LINKED_OBJECT'] != '' && ($properties[$i]['LINKED_PROPERTY'] == '' && $properties[$i]['LINKED_METHOD'] == '')) {
          $properties[$i]['LINKED_OBJECT'] = '';
      }
      SQLUpdate('vakio_info', $properties[$i]);
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] || $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
      }
     }
   }
   $out['PROPERTIES']=$properties;  
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
