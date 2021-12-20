<?php
/*
 * List zones
 */

if (!isset($adapter)) {
	exit;
}

$_GET['page'] = intval($_GET['page'] ?? 1);

$zones = new \Cloudflare\API\Endpoints\Zones($adapter);

try {
	$zones_data = $zones->listZones(false, false, $_GET['page']);
} catch (Exception $e) {
	echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
	return;
}
?>

<h3 class="d-none d-sm-block"><?php echo l('Home'); ?></h3>

<table class="table table-striped">
	<thead>
		<tr>
			<th scope="col"><?php echo l('Domain'); ?></th>
			<th scope="col" class="d-none d-sm-table-cell"><?php echo l('Status'); ?></th>
			<th scope="col" class="d-none d-sm-table-cell"><?php echo l('Operation'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($zones_data->result as $zone) {
			echo '<tr>';
			$_translate_manage = l('Manage');
			$_translate_manage_dns = l('Manage DNS');
			$_translate_security = l('Security');
			if (property_exists($zone, 'name_servers')) {
				echo <<<HTML
		<td scope="col">
			<div class="dropleft d-inline float-right d-sm-none">
				<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					{$_translate_manage}
				</button>
				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
					<a class="dropdown-item" href="https://dash.cloudflare.com/" target="_blank">{$_translate_manage_dns}</a>
				</div>
			</div>
			{$zone->name}
			<span class="d-block d-sm-none"> {$status_translate[$zone->status]}</span>
		</td>
HTML;
			} else {
				echo <<<HTML
		<td scope="col">
			<div class="dropleft d-inline float-right d-sm-none">
				<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					{$_translate_manage}
				</button>
				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
					<a class="dropdown-item" href="?action=zone&domain={$zone->name}&zoneid={$zone->id}">{$_translate_manage_dns}</a>
					<a class="dropdown-item" href="?action=security&domain={$zone->name}&zoneid={$zone->id}">{$_translate_security}</a>
				</div>
			</div>
			{$zone->name}
			<span class="d-block d-sm-none"> {$status_translate[$zone->status]}</span>
			</div>
		</td>
HTML;
			}

			echo <<<HTML
		<td class="d-none d-sm-table-cell">{$status_translate[$zone->status]}</td>
		<td class="d-none d-sm-table-cell btn-group" role="group">
HTML;
			if (property_exists($zone, 'name_servers')) {
				echo '<a href="https://dash.cloudflare.com/" target="_blank" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="' . l('This domain only supports NS setup. And you should manage DNS records on Cloudflare.com.') . '">' . l('Manage DNS') . '</a>';
			} else {
				echo <<<HTML
			<a href="?action=zone&domain={$zone->name}&zoneid={$zone->id}" class="btn btn-secondary btn-sm">{$_translate_manage_dns}</a>
			<a href="?action=security&domain={$zone->name}&zoneid={$zone->id}" class="btn btn-dark btn-sm">{$_translate_security}</a>
HTML;
			}
			echo '</td>';
		}
		?>
	</tbody>
</table>

<?php
if (isset($zones_data->result_info->total_pages)) {
	$previous_page = $next_page = '';
	if ($zones_data->result_info->page < $zones_data->result_info->total_pages) {
		$page_link = $zones_data->result_info->page + 1;
		$next_page = ' | <a href="?page=' . $page_link . '">' . l('Next') . '</a>';
	}
	if ($zones_data->result_info->page > 1) {
		$page_link = $zones_data->result_info->page - 1;
		$previous_page = '<a href="?page=' . $page_link . '">' . l('Previous') . '</a> | ';
	}
	echo '<p>' . $previous_page . l('Page') . ' ' . $zones_data->result_info->page . '/' . $zones_data->result_info->total_pages . $next_page . '</p>';
}
