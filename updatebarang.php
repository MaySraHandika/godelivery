<?php
/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'PATCH'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'PATCH method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data PATCH
 */
$formData = [];
parse_str(file_get_contents('php://input'), $formData);

$id_barang = $formData['id_barang'] ?? 0;
$jenis_barang = $formData['jenis_barang'] ?? '';
$nama_barang = $formData['nama_barang'] ?? '';
$photo_barang = $formData['photo_barang'] ?? '';
$stok = $formData['stok'] ?? 0;
$harga = $formData['harga'] ?? 0;
$deskripsi = $formData['deskripsi'] ?? '';

/**
 * Validation empty fields
 */

if(empty($id_barang)){
    $reply['error'] = 'ID Barang harus diisi';
    $isValidated = false;
}
if(empty($jenis_barang)){
    $reply['error'] = 'Jenis Barang harus diisi';
    $isValidated = false;
}
if(empty($nama_barang)){
    $reply['error'] = 'Nama Barang harus diisi';
    $isValidated = false;
}
if(empty($photo_barang)){
    $reply['error'] = 'Photo Barang harus diisi';
    $isValidated = false;
}
if(empty($stok)){
    $reply['error'] = 'Stok barang harus diisi';
    $isValidated = false;
}
if(empty($harga)){
    $reply['error'] = 'Harga Barang harus diisi';
    $isValidated = false;
}
if(empty($deskripsi)){
    $reply['error'] = 'Deskripsi Barang harus diisi';
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
/**
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM barang where id_barang = :id_barang";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':id_barang', $id_barang);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan ID Barang'.$id_barang;
        echo json_encode($reply);
        http_response_code(400);
        exit(0);
    }
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * Prepare query
 */
try{
    $fields = [];
    $query = "UPDATE barang SET jenis_barang = :jenis_barang, nama_barang = :nama_barang, photo_barang = :photo_barang, stok = :stok, harga = :harga, deskripsi = :deskripsi 
WHERE id_barang = :id_barang";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":id_barang", $id_barang);
    $statement->bindValue(":jenis_barang", $jenis_barang);
    $statement->bindValue(":nama_barang", $nama_barang);
    $statement->bindValue(":photo_barang", $photo_barang, PDO::PARAM_INT);
    $statement->bindValue(":stok", $stok);
    $statement->bindValue(":harga", $harga);
    $statement->bindValue(":deskripsi", $deskripsi, PDO::PARAM_INT);
    /**
     * Execute query
     */
    $isOk = $statement->execute();
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * If not OK, add error info
 * HTTP Status code 400: Bad request
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
 */
if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM barang where id_barang = :id_barang");
$stmSelect->bindValue(':id_barang', $id_barang);
$stmSelect->execute();
$dataBuku = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data kategori berdasarkan kolom kategori
 */
$dataFinal = [];
if($dataBuku) {
    $stmKategori = $connection->prepare("select * from kategori where id = :id");
    $stmKategori->bindValue(':id', $dataBuku['kategori']);
    $stmKategori->execute();
    $resultKategori = $stmKategori->fetch(PDO::FETCH_ASSOC);
    /*
     * Defulat kategori 'Tidak diketahui'
     */
    $kategori = [
        'id' => $dataBuku['kategori'],
        'nama' => 'Tidak diketahui'
    ];
    if ($resultKategori) {
        $kategori = [
            'id' => $resultKategori['id'],
            'nama' => $resultKategori['nama']
        ];
    }

    /*
     * Transoform hasil query dari table buku dan kategori
     * Gabungkan data berdasarkan kolom id kategori
     * Jika id kategori tidak ditemukan, default "tidak diketahui'
     */
    $dataFinal = [
        'isbn' => $dataBuku['isbn'],
        'judul' => $dataBuku['judul'],
        'pengarang' => $dataBuku['pengarang'],
        'tanggal' => $dataBuku['tanggal'],
        'jumlah' => $dataBuku['jumlah'],
        'created_at' => $dataBuku['created_at'],
        'kategori' => $kategori,
        'abstrak' => $dataBuku['abstrak'],
    ];
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);