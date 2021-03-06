<?php

defined('CORE') or exit;

/**
 * Input
 */
$crackerName = $_GET['cracker'] ?? null;
$page        = isset($_GET['spg']) ? intval($_GET['spg']) : 1;

/**
 * Releases count
 */
$query         = $db_link->query('SELECT COUNT(*) AS entries FROM releases;');
$totalReleases = $query->fetchColumn();

$totalCrackerReleases = 0;

if (!is_null($crackerName))
{
	$query = $db_link->prepare('SELECT COUNT(*) AS entries FROM releases WHERE cracker = ?;');

	$query->execute([ $crackerName ]);

	$totalCrackerReleases = $query->fetchColumn();
}

/**
 * Pagination
 */
$page = $page < 0 ? 0 : $page;

if ($page != 0)
{
	$premierMessageAafficher = ($page - 1) * $config['cracksparpage'];
}

$nombreDePages = (!is_null($crackerName)) ? ceil($totalCrackerReleases / $config['cracksparpage']) : ceil($totalReleases / $config['cracksparpage']);

/**
 * Releases
 */
$releases = null;

if (is_null($crackerName))
{
	if ($page === 0)
	{
		$releases = $db_link->prepare('SELECT * FROM releases');

		$releases->execute();
	}
	else
	{
		$releases = $db_link->prepare('SELECT * FROM releases ORDER BY date DESC LIMIT :offset, :limit;');

		$releases->bindParam('offset', $premierMessageAafficher, PDO::PARAM_INT);
		$releases->bindParam('limit', $config['cracksparpage'], PDO::PARAM_INT);

		$releases->execute();
	}
}
else
{
	if ($page === 0)
	{
		$releases = $db_link->prepare('SELECT * FROM releases WHERE cracker = ? ORDER BY date DESC;');

		$releases->execute([ $crackerName ]);
	}
	else
	{
		$releases = $db_link->prepare('SELECT * FROM releases WHERE cracker = :cracker ORDER BY date DESC LIMIT :offset, :limit;');

		$releases->bindParam('cracker', $crackerName, PDO::PARAM_STR);
		$releases->bindParam('offset', $premierMessageAafficher, PDO::PARAM_INT);
		$releases->bindParam('limit', $config['cracksparpage'], PDO::PARAM_INT);

		$releases->execute();
	}
}

?>
<h1>Rilisan <?php display($config['team']); ?> Yang Terbaru</h1>
<?php if (!is_null($crackerName)) { ?>
<h1>oleh <?php display($crackerName); ?></h1>
<?php } ?>
<p>
	Total Rilisan : <?php display($totalReleases); ?>
<?php if (!is_null($crackerName)) { ?>
	<br />
	Jumlah Rilisan milik <b><?php display($crackerName); ?></b> : <?php display($totalCrackerReleases); ?>
<?php } ?>
</p>
<hr />
<table border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td><center>Nama File</center></td>
		<td>Episode</td>
		<td>Tanggal</td>
	</tr>
<?php while ($release = $releases->fetch(PDO::FETCH_OBJ)){?>
	<tr>
		<td>
			<a href="<?php display($release->url); ?>" target="_blank"><?php display($release->name); ?></a>
		</td>
		<td>
			<a href="index.php?crk=releases&cracker=<?php display($release->cracker); ?>"><?php display($release->episode); ?></a>
		</td>
		<td><?php display(date("d M Y", strtotime($release->date))); ?></td>
	</tr>
<?php
	}
?>
</table>
<hr />
<center id="pagenums">Halaman :
<?php
	for ($i = 1 ; $i <= $nombreDePages ; $i++)
	{
		if ($i != $page){
			echo('<a href="' . $_SERVER['SCRIPT_NAME'] . '?crk=releases' . (!is_null($crackerName) ? '&cracker=' . htmlentities($crackerName, ENT_QUOTES) : '') . '&spg=' . $i . '">' . $i . '</a> ');
		}else{
			echo($i . ' ');
		}
	}
?>
</center>
<div id="footerlinks">
	<a href="<?php echo($_SERVER['SCRIPT_NAME']); ?>?crk=releases&spg=0"><font face="fixedsys">[Tampilkan Semua Rilisan]</font></a>
</div>
