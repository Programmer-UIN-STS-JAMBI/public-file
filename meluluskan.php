<!-- <meta http-equiv="refresh" content="300"> -->
<?php
#exit();
$NIM = $_GET['nim'];
$KD_TA = $_GET['kd_ta'];
$KD_SMT = $_GET['kd_smt'];
$TGL_YUDISIUM = "31/08/2016";
$ddcoco = 'password';
if ($KD_TA == '' or $KD_SMT == '' or $NIM == '') {
    echo "<center>&#128526;<br/>Nice landing captain </center>";
    exit();
}
function tgl_muna($NIM)
{
    global $conn3;
    $sql0 = "select NM_JENJANG from V_MAHASISWA where NIM = '$NIM'";
    $q0 = oci_parse($conn3, $sql0);
    oci_execute($q0);
    $r0 = oci_fetch_assoc($q0);
    if ($r0['NM_JENJANG'] == 'S3') {
        #S3 ambil dari periode 5
        $sql1 = "select TGL_MUNA as TGL from SKR_JADWAL_MUNAQOSAH@DBLINK_SKRIPSI where NIM = '$NIM' and PERIODE = '4'";
    } else {
        #S1,S2 ambil dari periode 3
        $sql1 = "select TGL_MUNA as TGL from SKR_JADWAL_MUNAQOSAH@DBLINK_SKRIPSI where NIM = '$NIM' and PERIODE = '2'";
    }
    $q1 = oci_parse($conn3, $sql1);
    oci_execute($q1);
    $r1 = oci_fetch_assoc($q1);
    return $r1['TGL'];
}
function kd_lulus($NIM)
{
    global $conn3, $KD_TA, $KD_SMT;
    $sql1 = "select P_LIHAT_IPK2013_AUTO(NIM) as IP,KD_PRODI from D_MAHASISWA where NIM = '$NIM' ";
    $q1 = oci_parse($conn3, $sql1);
    oci_execute($q1);
    $r1 = oci_fetch_assoc($q1);
    $IP = round($r1['IP'], 2);
    $KD_PRODI = $r1['KD_PRODI'];
    ////////////////
    $sql2 = "select KD_LULUS from D_SYARAT_PREDIKAT_LULUS2014 where KD_TA='$KD_TA' and KD_SMT = '$KD_SMT' and KD_PRODI = '$KD_PRODI' and IPK_MIN <= $IP and IPK_MAX >= $IP";
    $q2 = oci_parse($conn3, $sql2);
    oci_execute($q2);
    $r2 = oci_fetch_assoc($q2);
    return $r2['KD_LULUS'];
}
function judul($NIM)
{
    global $conn3;
    $sql1 = "select JUDUL as JUDUL_SKRIPSI,ABSTRAKSI as ABSTRAK from SKR_JADWAL_MUNAQOSAH@DBLINK_SKRIPSI where NIM = '$NIM'";
    $q1 = oci_parse($conn3, $sql1);
    oci_execute($q1);
    $r1 = oci_fetch_assoc($q1);
    return $r1;
}
$conn3 = oci_connect("sia", "$ddcoco", "ip:1521/sid");

$sql1 = "select to_char(TGL_YUDISIUM,'dd/mm/yyyy') as TGL,NIM from D_MHS_BEBASYUDISIUM 
where DIPROSES = '1' and TGL_YUDISIUM is not null and 
NIM not in (select NIM from D_ALUMNI) and NIM = '$NIM'";

$q1 = oci_parse($conn3, $sql1);
oci_execute($q1);

$err = '';
$NIM = '';
while ($r1 = oci_fetch_assoc($q1)) {
    $NIM = $r1['NIM'];
    $TGL_YUDISIUM = $r1['TGL'];
    $TGL_MUNA = tgl_muna($NIM);
    $skripsi = judul($NIM);
    $KD_LULUS = kd_lulus($NIM);
    if ($TGL_YUDISIUM == '') {
        $err .= "<li>TGL Yudisium masih kosong</li>";
    }
    if ($TGL_MUNA == '') {
        $err .= "<li>TGL Munaqosyah masih kosong</li>";
    }
    if ($KD_LULUS == '') {
        $err .= "<li>Predikat Lulus untuk Prodi ini belum ditentukan</li>";
    }
    if ($err != '') {
        echo "<ul>$err</ul>";
        exit();
    }
    $JUDUL = str_replace("'", "''", $skripsi['JUDUL_SKRIPSI']);
    //$JUDUL=addslashes($skripsi['JUDUL_SKRIPSI']);
    $ABSTRACT = str_replace("'", "''", $skripsi['ABSTRAK']);
    // echo "$NIM - $TGL_YUDISIUM - $TGL_MUNA - $JUDUL - $ABSTRACT - KD_LULUS $KD_LULUS<br/>";
    // echo "$i - $TGL_YUDISIUM - $TGL_MUNA - $NIM judul : ".strlen($JUDUL)." -- abs : ".strlen($ABSTRACT)." - $KD_LULUS<br/>";
    // exit();

    $NIP = 'ptipd';
    $NIM = $NIM;
    $TGL_LULUS = $TGL_MUNA;
    $TGL_YUDISIUM = $TGL_YUDISIUM;
    $KD_TA_LULUS = $KD_TA;
    $KD_SMT = $KD_SMT;
    $NO_IJAZAH = '';
    $JUDUL_TA = $JUDUL;
    $KD_LULUS = $KD_LULUS;
    $ABSTRAK_TA = $ABSTRACT;
    $PEKERJAAN = '';
    $KETERANGAN = "pksi" . $KD_TA . $KD_SMT;
    //////////////////
    /*
    NIP1            in VARCHAR2,
                        NIM1 		    IN D_ALUMNI.NIM%TYPE,
						TGL_LULUS1 	    IN VARCHAR2,
						TGL_YUDISIUM1 	IN VARCHAR2,
						KD_TA_LULUS1 	IN D_ALUMNI.KD_TA_LULUS%TYPE,
						KD_SMT1 	    IN D_ALUMNI.KD_SMT%TYPE,
 						NO_IJAZAH1 	    IN D_ALUMNI.NO_IJAZAH%TYPE,
 						JUDUL_TA1 	    IN D_ALUMNI.JUDUL_TA%TYPE,
 						KD_LULUS1 	    IN D_ALUMNI.KD_LULUS%TYPE,
 						ABSTRAK_TA1 	IN D_ALUMNI.ABSTRAK_TA%TYPE,
 						PEKERJAAN1 	    IN D_ALUMNI.PEKERJAAN%TYPE,
 						KETERANGAN1 
    */
    $sqlpro = 'BEGIN P_ISI_D_ALUMNI2014 (:NIP1,:NIM1,:TGL_LULUS1,:TGL_YUDISIUM1,:KD_TA_LULUS1,:KD_SMT1,:NO_IJAZAH1,:TGL_IJAZAH1,
    :JUDUL_TA1,:KD_LULUS1,:ABSTRAK_TA1,:PEKERJAAN1,:KETERANGAN1); END;';
    $stmt = oci_parse($conn3, $sqlpro);

    #$TGL_YUDISIUMX = '12/04/2016';
    oci_bind_by_name($stmt, ':NIP1', $NIP) or die('1');
    oci_bind_by_name($stmt, ':NIM1', $NIM) or die('2');
    oci_bind_by_name($stmt, ':TGL_LULUS1', $TGL_LULUS) or die('3');
    oci_bind_by_name($stmt, ':TGL_YUDISIUM1', $TGL_YUDISIUM) or die('4');
    oci_bind_by_name($stmt, ':KD_TA_LULUS1', $KD_TA_LULUS) or die('5');
    oci_bind_by_name($stmt, ':KD_SMT1', $KD_SMT) or die('6');
    oci_bind_by_name($stmt, ':NO_IJAZAH1', $NO_IJAZAH) or die('7');
    oci_bind_by_name($stmt, ':TGL_IJAZAH1', $TGL_YUDISIUM) or die('8');
    oci_bind_by_name($stmt, ':JUDUL_TA1', $JUDUL_TA) or die('9');
    oci_bind_by_name($stmt, ':KD_LULUS1', $KD_LULUS) or die('10');
    oci_bind_by_name($stmt, ':ABSTRAK_TA1', $ABSTRAK_TA) or die('11');
    oci_bind_by_name($stmt, ':PEKERJAAN1', $PEKERJAAN) or die('12');
    oci_bind_by_name($stmt, ':KETERANGAN1', $KETERANGAN) or die('13');

    /*echo "oci_bind_by_name($stmt,':NIP1',$NIP) or die('1');<br/>
    oci_bind_by_name($stmt,':NIM1',$NIM) or die('2');<br/>
	oci_bind_by_name($stmt,':TGL_LULUS1',$TGL_LULUS) or die('3');<br/>
	oci_bind_by_name($stmt,':TGL_YUDISIUM1',$TGL_YUDISIUM) or die('4');<br/>
	oci_bind_by_name($stmt,':KD_TA_LULUS1',$KD_TA_LULUS) or die('5');<br/>
	oci_bind_by_name($stmt,':KD_SMT1',$KD_SMT) or die('6');<br/>
    oci_bind_by_name($stmt,':NO_IJAZAH1',$NO_IJAZAH) or die('7');<br/>
	oci_bind_by_name($stmt,':TGL_IJAZAH1',$TGL_YUDISIUM) or die('8');<br/>
	oci_bind_by_name($stmt,':JUDUL_TA1',$JUDUL_TA) or die('9');<br/>
	oci_bind_by_name($stmt,':KD_LULUS1',$KD_LULUS) or die('10');<br/>
    oci_bind_by_name($stmt,':ABSTRAK_TA1',$ABSTRAK_TA) or die('11');<br/>
    oci_bind_by_name($stmt,':PEKERJAAN1',$PEKERJAAN) or die('12');<br/>
    oci_bind_by_name($stmt,':KETERANGAN1',$KETERANGAN) or die('13');";*/

    $hasil = oci_execute($stmt);
    if (!$hasil) {
        // $err = oci_error($conn3);
        echo "gagal dieksekusi untuk NIM $NIM <br/>";
        // print_r($err, true);
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    } else {
        echo "sukses untuk NIM $NIM<br/>";
    }
    oci_commit($conn3);
}
if ($NIM == '') {
    echo "NIM $NIM tidak dapat diproses, kemungkinan tidak ada di data mahasiswa sudah yudisium, atau sudah distatuskan lulus sebelumnya";
    exit();
}

$sql3 = "select count(*) as JUMLAH from D_ALUMNI where KETERANGAN = 'pksi20152'";
$q1 = oci_parse($conn3, $sql3);
oci_execute($q1);
$r1 = oci_fetch_assoc($q1);
echo "<br/>diproses " . date('d-m-Y H:i:s') . "<br/>--------------------------------------------<br/>JUMLAH " . $r1['JUMLAH'] . "";
?>