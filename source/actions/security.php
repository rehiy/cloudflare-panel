<?php
/*
 * Security page. (SSL and DNSSEC information)
 */

if (!isset($adapter)) {
	exit;
}

if (!isset($_GET['page'])) {
	$_GET['page'] = 1;
}

$dns = new Cloudflare\API\Endpoints\DNS($adapter);
$zones = new Cloudflare\API\Endpoints\Zones($adapter);

$zoneID = $_GET['zoneid'];
$zone_name = $_GET['domain'];

?>

<strong><?php echo '<h1 class="h5"><a href="?action=security&domain=' . $zone_name . '&zoneid=' . $zoneID . '">' . strtoupper($zone_name) . '</a></h1>'; ?></strong>

<hr>

<div class="am-scrollable-horizontal">
	<h3 id="ssl" class="mt-5 mb-3"><?php echo l('SSL Verify'); ?></h3>
	<?php
	try {
		$sslverify = $adapter->get('zones/' . $zoneID . '/ssl/verification?retry=true');
		$sslverify = json_decode($sslverify->getBody(), true)['result'];
	} catch (Exception $e) {
		$sslverify[0]['validation_method'] = 'http';
	}

	foreach ($sslverify as $sslv) {
		if ($sslv['validation_method'] == 'http' && isset($sslv['verification_info']['http_url']) && $sslv['verification_info']['http_url'] != '') {
	?>
			<h4><?php printf(l('HTTP File Verify for %s'), $sslv['hostname']); ?></h4>
			<p>URL: <code><?php echo $sslv['verification_info']['http_url']; ?></code></p>
			<p>Body: <code><?php echo $sslv['verification_info']['http_body']; ?></code></p>
			<?php
			if ($sslv['certificate_status'] != 'active') {
				echo '<p>' . l('SSL Status') . ': ' . $sslv['certificate_status'] . '</p>';
				if ($sslv['verification_status']) {
					echo '<p>' . l('Verify') . ': <span style="color:green;">' . l('Success') . '</span></p>';
				} else {
					echo '<p>' . l('Verify') . ': <span style="color:red;">' . l('Failed') . '</span></p>';
				}
			}
		} elseif ($sslv['validation_method'] == 'cname' || isset($sslv['verification_info']['record_name'])) {
			?>
			<h4><?php echo l('CNAME Verify'); ?></h4>
			<table class="am-table am-table-striped am-table-hover am-table-striped am-text-nowrap">
				<thead>
					<tr>
						<th><?php echo l('SSL Verification Record Name'); ?></th>
						<th>CNAME</th>
					</tr>
				</thead>
				<tbody>
					<?php echo '<tr>
						<td><code>' . $sslv['verification_info']['record_name'] . '</code></td>
						<td><code>' . $sslv['verification_info']['record_target'] . '</code></td>
						</tr>';
					?>
				</tbody>
			</table>
	<?php
			if ($sslv['certificate_status'] != 'active') {
				echo '<p>' . l('SSL Status') . ': ' . $sslv['certificate_status'] . '</p>';
				if ($sslv['verification_status']) {
					echo '<p>' . l('Verify') . ': <span style="color:green;">' . l('Success') . '</span></p>';
				} else {
					echo '<p>' . l('Verify') . ': <span style="color:red;">' . l('Failed') . '</span></p>';
				}
			}
		} elseif ($sslv['validation_method'] == 'http') {
			if (isset($sslv['hostname'])) {
				echo '<h4>' . $sslv['hostname'] . '</h4>';
			}
			echo l('<p style="color:green;">No error for SSL.</p><p>Just point the record(s) to Cloudflare and the SSL certificate will be issued and renewed automatically.</p>');
		} else {
			echo '<h4>Unknown Verification</h4><pre>';
			print_r($sslv['verification_info']);
			echo '</pre>';
			if ($sslv['certificate_status'] != 'active') {
				echo '<p>' . l('SSL Status') . ': ' . $sslv['certificate_status'] . '</p>';
				if ($sslv['verification_status']) {
					echo '<p>' . l('Verify') . ': <span style="color:green;">' . l('Success') . '</span></p>';
				} else {
					echo '<p>' . l('Verify') . ': <span style="color:red;">' . l('Failed') . '</span></p>';
				}
			}
		}
	}
	?>

	<h3 class="mt-5 mb-3"><?php echo l('DNSSEC <small>(Only for NS setup)</small>'); ?></h3>

	<?php
	echo '<p>' . l('This feature is designed for users who use Cloudflare DNS setup. If you are using third-party DNS services, do not turn it on nor add DS record, otherwise your domain may become inaccessible.') . '</p>';

	try {
		$dnssec = $adapter->get('zones/' . $zoneID . '/dnssec');
		$dnssec = json_decode($dnssec->getBody());
	} catch (Exception $e) {
		echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
		return;
	}

	if ($dnssec->result->status == 'active') {
		echo '<p style="color:green;">' . l('Activated') . '</p><p>DS：<code>' . $dnssec->result->ds . '</code></p><p>Public Key：<code>' . $dnssec->result->public_key . '</code></p>';
		echo '<p><a href="?action=dnssec&zoneid=' . $zoneID . '&domain=' . $zone_name . '&do=disabled">' . l('Deactivate') . '</a></p>';
	} elseif ($dnssec->result->status == 'pending') {
		echo '<p style="color:orange;">' . l('Pending') . '</p><p>DS：<code>' . $dnssec->result->ds . '</code></p><p>Public Key：<code>' . $dnssec->result->public_key . '</code></p>';
		echo '<p><a href="?action=dnssec&zoneid=' . $zoneID . '&domain=' . $zone_name . '&do=disabled">' . l('Deactivate') . '</a></p>';
	} else {
		echo '<p style="color:red;">' . l('Not Activated') . '</p>';
		echo '<p><a href="?action=dnssec&zoneid=' . $zoneID . '&domain=' . $zone_name . '&do=active" onclick="return confirm(\'' . l('This feature is designed for users who use Cloudflare DNS setup. If you are using third-party DNS services, do not turn it on nor add DS record, otherwise your domain may become inaccessible.') . '\')">' . l('Activate') . '</a></p>';
	}
	?>
</div>