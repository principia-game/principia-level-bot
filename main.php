#!/usr/bin/php
<?php
require 'mastodon.php';
require 'config.php';
require $archive_dir.'/conf/config.php';
require $archive_dir.'/lib/mysql.php';
require $archive_dir.'/lib/levellist.php';

$mastodon = new MastodonAPI($token, $base_url);

while (true) {
	$level = fetch("SELECT l.*,u.id u_id,u.name u_name FROM levels l JOIN users u ON l.author = u.id WHERE l.id = ?",
		[$publicLevels[array_rand($publicLevels)]]);

	if (!$level)
		continue;

	$thumb = "{$archive_dir}/data/thumbs/{$level['id']}-0-0.jpg";

	if (file_exists($thumb))
		break;
}


$status = sprintf(<<<TEXT
Title: %s
Author: %s
Published on: %s

https://archive.principia-web.se/level/%d
TEXT, $level['title'], $level['u_name'], date('j F Y', $level['time']), $level['id']);

print($status);

$response = $mastodon->uploadMedia([
	'file' => curl_file_create($thumb, 'image/jpg', "level_thumb_{$level['id']}.jpg"),
	'description' => "Thumbnail of the level."
]);

$mastodon->postStatus([
	'status'      => $status,
	'visibility'  => 'unlisted',
	'language'    => 'en',
	'media_ids[]' => $response['id'],
]);
