<?php
/*
 * Delete a specific record for a domain
 */

if (!isset($adapter)) {
	exit;
}

$zoneId = $_GET['zoneid'] ?? '';
$zoneName = $_GET['domain'] ?? '';
$recordId = $_GET['delete'] ?? '';

$dns = new \Cloudflare\API\Endpoints\DNS($adapter);

try {
	if ($dns->deleteRecord($zoneId, $recordId)) {
		echo '<p class="alert alert-success" role="alert">' . l('Success') . '! <a href="?action=zone&domain=' . $zoneName . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
	} else {
		echo '<p class="alert alert-danger" role="alert">' . l('Failed') . '! <a href="?action=zone&domain=' . $zoneName . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
	}
} catch (Exception $e) {
	echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
}
