<?php
/*
 * Zone setup page
 */

if (!isset($adapter)) {
	exit;
}

$zoneId = $_GET['zoneid'] ?? '';
$zoneName = $_GET['domain'] ?? '';

$page = intval($_GET['page'] ?? 1);

$enable = $_GET['enable'] ?? null;
$disable = $_GET['disable'] ?? null;

$dns = new Cloudflare\API\Endpoints\DNS($adapter);
$zones = new Cloudflare\API\Endpoints\Zones($adapter);

try {
	$dnsresult_data = $dns->listRecords($zoneId, false, false, false, $page);
} catch (Exception $e) {
	echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
	return;
}

$dnsresult = $dnsresult_data->result;
$zone_name = htmlspecialchars($zoneName);

foreach ($dnsresult as $record) {
	$zone_name = $record->zone_name;
	$dnsids[$record->id] = true;
	$dnsproxyied[$record->id] = $record->proxied;
	$dnstype[$record->id] = $record->type;
	$dnscontent[$record->id] = $record->content;
	$dnsname[$record->id] = $record->name;
	$dnscheck[$record->name] = true;
}
?>

<strong><?php echo '<h1 class="h5"><a href="?action=zone&domain=' . $zone_name . '&zoneid=' . $zoneId . '">' . strtoupper($zone_name) . '</a></h1>'; ?></strong>

<hr>

<?php
/* Toggle the CDN */
if (isset($enable) && !$dnsproxyied[$enable]) {
	if ($dns->updateRecordDetails($zoneId, $enable, ['type' => $dnstype[$enable], 'content' => $dnscontent[$enable], 'name' => $dnsname[$enable], 'proxied' => true])->success == true) {
		echo '<p class="alert alert-success" role="alert">' . l('Success') . '! </p>';
	} else {
		echo '<p class="alert alert-danger" role="alert">' . l('Failed') . '! </p><p><a href="?action=zone&domain=' . $zone_name . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
		return;
	}
} else {
	$enable = 1;
	if (isset($disable) && $dnsproxyied[$disable]) {
		if ($dns->updateRecordDetails($zoneId, $disable, ['type' => $dnstype[$disable], 'content' => $dnscontent[$disable], 'name' => $dnsname[$disable], 'proxied' => false])->success == true) {
			echo '<p class="alert alert-success" role="alert">' . l('Success!') . '</p>';
		} else {
			echo '<p class="alert alert-danger" role="alert">' . l('Failed') . '! </p><p><a href="?action=zone&domain=' . $zone_name . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
			return;
		}
	} else {
		$disable = 1;
	}
}
?>

<div class="btn-group dropright">
	<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<?php echo l('Contents'); ?>
	</button>
	<div class="dropdown-menu">
		<a class="dropdown-item" href="#dns"><?php echo l('DNS Management'); ?></a>
		<a class="dropdown-item" href="#cname"><?php echo l('CNAME Setup'); ?></a>
		<a class="dropdown-item" href="#ip"><?php echo l('IP Setup'); ?></a>
		<a class="dropdown-item" href="#ns"><?php echo l('NS Setup'); ?></a>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="https://dash.cloudflare.com/" target="_blank"><?php echo l('More Settings'); ?></a>
	</div>
</div>
<h3 class="mt-5 mb-3" id="dns"><?php echo l('DNS Management'); ?>
	<a class="btn btn-primary float-sm-right d-block mt-3 mt-sm-0" href='?action=add_record&zoneid=<?php echo $zoneId; ?>&domain=<?php echo $zone_name; ?>'><?php echo l('Add New Record'); ?></a>
</h3>
<table class="table table-striped">
	<thead>
		<tr>
			<th scope="col" class="d-none d-md-table-cell"><?php echo l('Record Type'); ?></th>
			<th scope="col"><?php echo l('Host Name'); ?></th>
			<th scope="col" class="d-none d-md-table-cell"><?php echo l('Content'); ?></th>
			<th scope="col" class="d-none d-md-table-cell"><?php echo l('TTL'); ?></th>
			<th scope="col" class="d-none d-md-table-cell"><?php echo l('Operation'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$no_record_yet = true;
		foreach ($dnsresult as $record) {
			if ($record->proxiable) {
				if ($record->proxied) {
					$proxiable = '<a href="?action=zone&domain=' . $zone_name . '&disable=' . $record->id . '&page=' . $page . '&zoneid=' . $zoneId . '"><img src="assets/cloud_on.png" height="30"></a>';
				} else {
					$proxiable = '<a href="?action=zone&domain=' . $zone_name . '&enable=' . $record->id . '&page=' . $page . '&zoneid=' . $zoneId . '"><img src="assets/cloud_off.png" height="30"></a>';
				}
			} else {
				$proxiable = '<img src="assets/cloud_off.png" height="30">';
			}
			if (isset($enable) && $record->id === $enable) {
				$proxiable = '<a href="?action=zone&domain=' . $zone_name . '&disable=' . $record->id . '&page=' . $page . '&zoneid=' . $zoneId . '"><img src="assets/cloud_on.png" height="30"></a>';
			} elseif (isset($disable) && $record->id === $disable) {
				$proxiable = '<a href="?action=zone&domain=' . $zone_name . '&enable=' . $record->id . '&page=' . $page . '&zoneid=' . $zoneId . '"><img src="assets/cloud_off.png" height="30"></a>';
			}
			if ($record->type == 'MX') {
				$priority = '<code>' . $record->priority . '</code> ';
			} else {
				$priority = '';
			}
			if (isset($ttl_translate[$record->ttl])) {
				$ttl = $ttl_translate[$record->ttl];
			} else {
				$ttl = $record->ttl . ' s';
			}
			$no_record_yet = false;
			echo '
			<tr>
				<td class="d-none d-md-table-cell"><code>' . $record->type . '</code></td>
				<td scope="col">
					<div class="d-block d-md-none float-right">' . $proxiable . '</div>
					<div class="d-block d-md-none">' . $record->type . ' ' . l('record') . '</div>
					<code>' . htmlspecialchars($record->name) . '</code>
					<div class="d-block d-md-none">' . l('points to') . ' ' . '<code>' . htmlspecialchars($record->content) . '</code></div>
					<div class="btn-group dropleft float-right d-block d-md-none" style="margin-top:-1em;">
						<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						' . l('Manage') . '
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="?action=edit_record&domain=' . $zone_name . '&recordid=' . $record->id . '&zoneid=' . $zoneId . '">' . l('Edit') . '</a>
							<a class="dropdown-item" href="?action=delete_record&domain=' . $zone_name . '&delete=' . $record->id . '&zoneid=' . $zoneId . '" onclick="return confirm(\'' . l('Are you sure to delete') . ' ' . htmlspecialchars($record->name) . '?\')">' . l('Delete') . '</a>
						</div>
					</div>
					<div class="d-block d-md-none">' . l('TTL') . ' ' . $ttl . '</div>
				</td>
				<td class="d-none d-md-table-cell">' . $priority . '<code>' . htmlspecialchars($record->content) . '</code></td>
				<td class="d-none d-md-table-cell">' . $ttl . '</td>
				<td class="d-none d-md-table-cell" style="width: 200px;">' . $proxiable . ' |
					<div class="btn-group" role="group">
						<a class="btn btn-dark btn-sm" href="?action=edit_record&domain=' . $zone_name . '&recordid=' . $record->id . '&zoneid=' . $zoneId . '">' . l('Edit') . '</a>
						<a class="btn btn-danger btn-sm" href="?action=delete_record&domain=' . $zone_name . '&delete=' . $record->id . '&zoneid=' . $zoneId . '" onclick="return confirm(\'' . l('Are you sure to delete') . ' ' . htmlspecialchars($record->name) . '?\')">' . l('Delete') . '</a>
					</div>
				</td>
			</tr>
			';
		}
		?>
	</tbody>
</table>

<?php
if ($no_record_yet) {
	echo '<p class="alert alert-warning" role="alert">' . l('There is no record in this zone yet. Please add some!') . '</p>';
}

if (isset($dnsresult_data->result_info->total_pages)) {
	$previous_page = $next_page = '';
	if ($dnsresult_data->result_info->page < $dnsresult_data->result_info->total_pages) {
		$page_link = $dnsresult_data->result_info->page + 1;
		$next_page = ' | <a href="?action=zone&domain=' . $zone_name . '&page=' . $page_link . '&zoneid=' . $zoneId . '">' . l('Next') . '</a>';
	}
	if ($dnsresult_data->result_info->page > 1) {
		$page_link = $dnsresult_data->result_info->page - 1;
		$previous_page = '<a href="?action=zone&domain=' . $zone_name . '&page=' . $page_link . '&zoneid=' . $zoneId . '">' . l('Previous') . '</a> | ';
	}
	echo '<p>' . $previous_page . l('Page') . ' ' . $dnsresult_data->result_info->page . '/' . $dnsresult_data->result_info->total_pages . $next_page . '</p>';
}
?>

<br><br>
<p><strong><?php echo l('You can use CNAME, IP or NS to set it up.'); ?></strong></p>

<h3 class="mt-5 mb-3" id="cname"><?php echo l('CNAME Setup'); ?></h3>
<table class="table table-striped">
	<thead>
		<tr>
			<th scope="col"><?php echo l('Host Name'); ?></th>
			<th scope="col" class="d-none d-md-table-cell">CNAME</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$avoid_cname_duplicated = [];
		$last_domain = '';
		foreach ($dnsresult as $record) {
			if (!isset($avoid_cname_duplicated[$record->name])) {
				$last_subdomain = $record->name;
				echo '
				<tr>
					<td scope="col"><code>' . $record->name . '</code>
						<div class="d-block d-md-none">' . l('points to') . ' <code>' . $record->name . '.cdn.cloudflare.net</code></div>
					</td>
					<td class="d-none d-md-table-cell"><code>' . $record->name . '.cdn.cloudflare.net</code></td>
				</tr>
				';
				$avoid_cname_duplicated[$record->name] = true;
			}
		}
		?>
	</tbody>
</table>

<?php
if ($no_record_yet) {
	echo '<p class="alert alert-warning" role="alert">' . l('There is no record in this zone yet. Please add some!') . '</p>';
}

if (isset($dnsresult_data->result_info->total_pages)) {
	$previous_page = $next_page = '';
	if ($dnsresult_data->result_info->page < $dnsresult_data->result_info->total_pages) {
		$page_link = $dnsresult_data->result_info->page + 1;
		$next_page = ' | <a href="?action=zone&domain=' . $zone_name . '&page=' . $page_link . '">' . l('Next') . '</a>';
	}
	if ($dnsresult_data->result_info->page > 1) {
		$page_link = $dnsresult_data->result_info->page - 1;
		$previous_page = '<a href="?action=zone&domain=' . $zone_name . '&page=' . $page_link . '">' . l('Previous') . '</a> | ';
	}
	echo '<p>' . $previous_page . l('Page') . ' ' . $dnsresult_data->result_info->page . '/' . $dnsresult_data->result_info->total_pages . $next_page . '</p>';
}

if ($last_subdomain != '') {
	try {
		$resolver = new Net_DNS2_Resolver(array('nameservers' => array('173.245.59.31', '2400:cb00:2049:1::adf5:3b1f')));
		$resp = $resolver->query($zone_name, 'NS');
		$resp_a = $resolver->query($last_subdomain . '.cdn.cloudflare.net', 'A');
		$resp_aaaa = $resolver->query($last_subdomain . '.cdn.cloudflare.net', 'AAAA');
	} catch (Net_DNS2_Exception $e) {
		// echo $e->getMessage();
	}
}

if (
	$last_subdomain != '' && (isset($resp_a->answer[0]->address) && isset($resp_a->answer[1]->address)) ||
	(isset($resp_aaaa->answer[0]->address) && isset($resp_aaaa->answer[1]->address))
) {
?>
	<h3 class="mt-5 mb-3" id="ip"><?php echo l('IP Setup'); ?></h3>
	<?php
	if (isset($resp_a->answer[0]->address) && isset($resp_a->answer[1]->address)) {
	?>
		<h4>Anycast IPv4</h4>
		<ul>
			<li><code><?php echo $resp_a->answer[0]->address; ?></code></li>
			<li><code><?php echo $resp_a->answer[1]->address; ?></code></li>
		</ul>
	<?php
	}
	if (isset($resp_aaaa->answer[0]->address) && isset($resp_aaaa->answer[1]->address)) {
	?>
		<h4>Anycast IPv6</h4>
		<ul>
			<li><code><?php echo $resp_aaaa->answer[0]->address; ?></code></li>
			<li><code><?php echo $resp_aaaa->answer[1]->address; ?></code></li>
		</ul>
<?php
	}
}
?>

<?php
if ($last_subdomain != '' && isset($resp->answer[0]->nsdname) && isset($resp->answer[1]->nsdname)) {
?>
	<h3 class="mt-5 mb-3" id="ns"><?php echo l('NS Setup'); ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col"><?php echo l('Host Name'); ?></th>
				<th class="d-none d-md-table-cell">NS</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><code><?php echo $zone_name; ?></code>
					<div class="d-block d-md-none"><?php echo l('points to') . ' <code>' . $resp->answer[0]->nsdname . '</code>' ?></div>
				</td>
				<td class="d-none d-md-table-cell"><code><?php echo $resp->answer[0]->nsdname; ?></code></td>
			</tr>
			<tr>
				<td><code><?php echo $zone_name; ?></code>
					<div class="d-block d-md-none"><?php echo l('points to') . ' <code>' . $resp->answer[1]->nsdname . '</code>' ?></div>
				</td>
				<td class="d-none d-md-table-cell"><code><?php echo $resp->answer[1]->nsdname; ?></code></td>
			</tr>
		</tbody>
	</table>
<?php } ?>

<hr>

<h3 class="mt-5 mb-3"><a href="https://dash.cloudflare.com/" target="_blank"><?php echo l('More Settings'); ?></a></h3>
<p><?php echo l('This site only provides configurations that the official does not have. For more settings, such as Page Rules, Crypto, Firewall, Cache, etc., please use the same account to login Cloudflare.com to setup. '); ?><a href="https://dash.cloudflare.com/" target="_blank"><?php echo l('More Settings'); ?></a></p>