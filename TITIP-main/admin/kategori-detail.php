<?php 
    require "../config.php";

    $kode = $_GET['k'];

    $query = mysqli_query($con, "SELECT * FROM kategori WHERE kode=$kode");
    $data = mysqli_fetch_array($query);

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
</head>
<body>
    <?php require "navbar.php"; ?>
    <?php require "sidebar.php"; ?>

    <div class="container">
        <h2>Detail Kategori</h2>
        <div class="col-12 col-md-6">
            <form action="" method="post">
                <div>
                    <label for="kategori">Kategori</label>
                    <input type="text" name="kategori" kode="kategori" class="form-control" value="<?php echo $data ['nama']?>">
                </div>

                <div class="mt-5 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary" name="editBtn">Edit</button>
                    <button type="submit" class="btn btn-danger" name="deleteBtn">Hapus</button>
                </div>
            </form>

            <?php
                if(isset($_POST['editBtn'])){
                    $kategori=htmlspecialchars($_POST['kategori']);

                    if($data['nama']==$kategori){
                        ?>
                            <meta http-equiv="refresh" content="0; url=kategori.php"/>
                        <?php
                    }
                    else{
                        $query = mysqli_query($con, "SELECT * FROM kategori WHERE nama='$kategori'");
                        $jumlahData = mysqli_num_rows($query);

                        if($jumlahData > 0){
                            ?>
                            <div class="alert alert-dark mt-3" role="alert">
                                Fuck!! Kategori sudah ada
                            </div>
                            <?php
                        }
                        else{
                            $querySimpan = mysqli_query($con, "UPDATE kategori SET nama='$kategori' WHERE kode='$kode'");

                        if($querySimpan){
                            ?>
                            <div class="alert alert-primary mt-3" role="alert">
                                Yey!! Berhasil!!
                            </div>
                            <meta http-equiv="refresh" content="1; url=kategori.php"/>  
                            <?php

                            }
                            else{
                                echo mysqli_error($con);
                            }
                        }
                    }
                }

                if(isset($_POST['deleteBtn'])){
                    // Cek apakah kategori memiliki produk terkait
                    $queryCheck = mysqli_query($con, "SELECT * FROM produk WHERE kategori_kode='$kode'");
                    $dataHitung = mysqli_num_rows($queryCheck);
                    
                    if($dataHitung > 0){
                        
                        if($dataHitung){
                            ?>
                            <div class="alert alert-warning mt-3" role="alert">
                                Kategori tidak bisa dihapus karna digunakan produk.
                            </div>
                            <?php
                            die();                                                              
                        } else {
                            echo mysqli_error($con);
                        }
                    }
                
                    // Hapus kategori setelah produk terkait dihapus
                    $queryDelete = mysqli_query($con, "DELETE FROM kategori WHERE kode='$kode'");
                    if($queryDelete){
                        ?>
                        <div class="alert alert-primary mt-3" role="alert">
                            Kategori berhasil dihapus.
                        </div>
                        <meta http-equiv="refresh" content="1; url=kategori.php"/>
                        <?php
                    } else {
                        echo mysqli_error($con);
                    }
                }
                
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
</body>
</html>