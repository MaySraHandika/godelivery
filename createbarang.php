<?php
include 'connection.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}

// get input data post

$id_barang = $_POST ['id_barang'];
$jenis_barang = $_POST['jenis_barang'] ??'';
$nama_barang = $_POST['nama_barang'] ??'';
$photo_barang = $_POST['photo_barang']??'';
$stok = $_POST['stok']??'';
$harga = $_POST['harga']??'';
$deskripsi = $_POST['deskripsi']??'';

$isValidated = true;
if(empty($id_barang)){
    $reply['error'] = 'id barang harus diisi';
    $isValidated = false;
}
if(empty($jenis_barang)){
    $reply['error'] = 'jenis barang harus diisi';
    $isValidated = false;
}
if(empty($nama_barang)){
    $reply['error'] = 'nama barang harus diisi';
    $isValidated = false;
}
if(empty($photo_barang)){
    $reply['error'] = 'sertakan photo barang';
    $isValidated = false;
}
if(empty($stok)){
    $reply['error'] = 'isi stok barang';
    $isValidated = false;
}
if(empty($harga)){
    $reply['error'] = 'harga harus diisi';
    $isValidated = false;
}
if(empty($deskripsi)){
    $reply['error'] = 'deskripsi harus diisi';
    $isValidated = false;
}

/*
 * Jika filter gagal
 */
if(!$isValidated){
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $query ="INSERT INTO barang (id_barang,jenis_barang, nama_barang, photo_barang, stok, harga, deskripsi) VALUES (:id_barang,:jenis_barang, :nama_barang, :photo_barang, :stok, :harga, :harga, :deskripsi)";
    /** @var TYPE_NAME $connection */
    $statement = $connection->prepare($query);

    /**
     * Bind params
     */
    $statement->bindValue(":id_barang",$id_barang);
    $statement->bindValue(":jenis_barang", $jenis_barang);
    $statement->bindValue(":nama_barang", $nama_barang);
    $statement->bindValue(":photo_barang", $photo_barang);
    $statement->bindValue(":stok", $stok);
    $statement->bindValue(":harga", $harga);
    $statement->bindValue(":deskripsi", $deskripsi);

    /**
     * Execute query
     */
    $isOk = $statement->execute();


}catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}
$getResult = "SELECT * FROM barang WHERE id_barang = :id_barang";
$stm = $connection->prepare($getResult);
$stm->bindValue(':id_barang', $id_barang);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Transform result
 */
$dataFinal = [
    'id_barang' => $result['id_barang'],
    'jenis_barang' => $result['jenis_barang'],
    'nama_barang' => $result['nama_barang'],
    'photo_barang' => $result['photo_barang'],
    'stok' => $result['stok'],
    'harga' => $result['harga'],
    'deskripsi' =>$result['deskripsi']
];
/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);
