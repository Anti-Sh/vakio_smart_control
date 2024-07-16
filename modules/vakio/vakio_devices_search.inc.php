<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['vakio_devices_qry'];
  } else {
   $session->data['vakio_devices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_vakio_devices="ID DESC";
  $out['SORTBY']=$sortby_vakio_devices;
  $res=SQLSelect("SELECT * FROM vakio_devices WHERE $qry ORDER BY ".$sortby_vakio_devices);
  // Создаём список с наименованием типов устройств
  $devices = array(
    0 => array(
      "TITLE"=>"Atmosphere",
    ),
    1 => array(
      "TITLE"=>"Base Smart",
    ),
    2 => array(
      "TITLE"=>"Kiv Pro/New",
    ),
    3 => array(
      "TITLE"=>"Openair",
    ),
  );
  if (isset($res[0]['ID'])) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
	$res[$i]['VAKIO_DEVICE_TYPE']=$devices[$res[$i]['VAKIO_DEVICE_TYPE']]['TITLE'];
   }
   $out['RESULT']=$res;
  }
