<?php
include("config.php");

function client_error($msg) {
  header('Status: 400 Bad Request');
  echo $msg;
  flush();
  return false;
}

function server_error($msg) {
  header('Status: 500 Internal Server Error');
  echo $msg;
  flush();
  return false;
}

function validateStmt($stmt) {
  if (!$stmt) return server_error('Unable to query database. Please try again later.');
  return true;
}

function validateResult($result) {
  if (!$result) return server_error('Unable to retrieve data. Please try again later.');
  return true;
}

function validateCallback($callback) {
  if (!preg_match('/^[a-z_][a-z0-9_]+$/i', $callback)) return client_error('Invalid callback.');
  $reserved = ",abstract,boolean,break,byte,case,catch,char,class,const,continue,default,do,double,else,extends,false,final,finally	float,for,function,goto,if,implements,import,in,instanceof,int,interface,long,native,new,null,package,private,protected	public,return,short,static,super,switch,synchronized,this,throw,throws,transient,true,try,var,void,while,with,";
  if (strpos($reserved, ",$callback,") !== false) return client_error('Callback cannot be reserved word.');
  return true;
}

$sql = <<<EOL
SELECT
  latitude,
  longitude,
  name,
  url,
  type,
  image,
  patients,
  encounters,
  observations,
  contact,
  email,
  notes,
  CASE WHEN date_changed IS NULL THEN '' ELSE date_changed END as date_changed,
  date_created
FROM
  atlas
EOL
;

$dbh = new PDO($db_dsn, $db_username, $db_password);
$stmt = $dbh->query($sql);
if (!validateStmt($stmt))
  exit;
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!validateResult($result))
  exit;

if (array_key_exists('callback', $_GET)) {
  $callback = $_GET['callback'];
  if (!validateCallback($callback))
    exit;
} else {
  $callback = false;
}

if ($callback)
  echo "$callback(";
echo json_encode($result);
if ($callback)
  echo ");";

?>