<?php
declare(strict_types=1);
$dir=__DIR__.'/storage'; if(!is_dir($dir)) mkdir($dir,0775,true);
$sessions=$dir.'/sessions'; if(!is_dir($sessions)) mkdir($sessions,0775,true);
session_save_path($sessions); session_start(); header('Content-Type: application/json; charset=utf-8');
$db=new PDO('sqlite:'.$dir.'/botanica.sqlite'); $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $db->exec('PRAGMA foreign_keys=ON');
$db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT NOT NULL UNIQUE,password TEXT NOT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
$db->exec('CREATE TABLE IF NOT EXISTS onboarding_profiles (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL UNIQUE,has_garden INTEGER NOT NULL DEFAULT 0,garden_plants TEXT,has_pots INTEGER NOT NULL DEFAULT 0,pot_plants TEXT,interests TEXT NOT NULL,goal TEXT NOT NULL,completed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)');
function body():array{return json_decode(file_get_contents('php://input'),true)?:[];} function out(array $d,int $s=200):never{http_response_code($s);echo json_encode($d);exit;}
function loggedUser(PDO $db):?array{if(empty($_SESSION['user_id']))return null;$s=$db->prepare('SELECT id,name,email FROM users WHERE id=?');$s->execute([$_SESSION['user_id']]);return $s->fetch(PDO::FETCH_ASSOC)?:null;}
function profile(PDO $db,int $id):?array{$s=$db->prepare('SELECT has_garden,garden_plants,has_pots,pot_plants,interests,goal FROM onboarding_profiles WHERE user_id=?');$s->execute([$id]);$p=$s->fetch(PDO::FETCH_ASSOC);if(!$p)return null;foreach(['garden_plants','pot_plants','interests'] as $k)$p[$k]=json_decode($p[$k]?:'[]',true);return $p;}
$action=$_GET['action']??'';$data=body();
try{
 if($action==='status'){$u=loggedUser($db);out(['user'=>$u,'profile'=>$u?profile($db,(int)$u['id']):null]);}
 if($action==='register'){$name=trim($data['name']??'');$email=strtolower(trim($data['email']??''));$pass=$data['password']??'';if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<6)out(['error'=>'Completa tu nombre, un correo válido y una contraseña de al menos 6 caracteres.'],422);$s=$db->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');$s->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);$_SESSION['user_id']=(int)$db->lastInsertId();out(['ok'=>true]);}
 if($action==='login'){$s=$db->prepare('SELECT * FROM users WHERE email=?');$s->execute([strtolower(trim($data['email']??''))]);$u=$s->fetch(PDO::FETCH_ASSOC);if(!$u||!password_verify($data['password']??'',$u['password']))out(['error'=>'Correo o contraseña incorrectos.'],401);$_SESSION['user_id']=(int)$u['id'];out(['ok'=>true]);}
 if($action==='logout'){session_destroy();out(['ok'=>true]);}
 $u=loggedUser($db);if(!$u)out(['error'=>'Inicia sesión para continuar.'],401);
 if($action==='save_profile'){$interests=array_values(array_filter($data['interests']??[]));$goal=trim($data['goal']??'');if(!$interests||$goal==='')out(['error'=>'Elige al menos un interés y un objetivo.'],422);$s=$db->prepare('INSERT INTO onboarding_profiles(user_id,has_garden,garden_plants,has_pots,pot_plants,interests,goal) VALUES(?,?,?,?,?,?,?) ON CONFLICT(user_id) DO UPDATE SET has_garden=excluded.has_garden,garden_plants=excluded.garden_plants,has_pots=excluded.has_pots,pot_plants=excluded.pot_plants,interests=excluded.interests,goal=excluded.goal,completed_at=CURRENT_TIMESTAMP');$s->execute([(int)$u['id'],!empty($data['has_garden'])?1:0,json_encode([]),!empty($data['has_pots'])?1:0,json_encode([]),json_encode($interests),$goal]);out(['ok'=>true]);}
 out(['error'=>'Acción no encontrada.'],404);
}catch(PDOException $e){out(['error'=>str_contains($e->getMessage(),'UNIQUE')?'Ya existe una cuenta con ese correo.':'No fue posible guardar la información.'],422);}
