<?php

use \RedBeanPHP\R as R;

$account = $this->getAccount();

$output = tmpfile();

fputcsv($output, ["id", "title", "body", "subreddit", "date", "time", "timezone", "nsfw", "sendreplies", "delete"]);

foreach ($account->ownPostList as $post) {
  // Skip posts that have been submitted or had an error.
  if (isset($post->url) || isset($post->error)) {
    continue;
  }

  $postDateTime = new \DateTime();
  $postDateTime->setTimestamp($post->when);
  $postDateTime->setTimezone(new \DateTimeZone($post->whenzone));
  $dateString = $postDateTime->format("Y-m-d");
  $timeString = $postDateTime->format("H:i");

  fputcsv($output,
    [ $post->id,
      $post->title,
      $post->body,
      $post->subreddit,
      $dateString,
      $timeString,
      $post->whenzone,
      $post->nsfw,
      $post->sendreplies,
      @$post->deleted ?? 0
    ]);
}

// Reddit account names should only include A-Z, a-z, 0-9, _ and -.
// All of these are ok in file names.
$accountName = $account->name;


// Erase the output buffer contents in case anything ended up in there.
ob_end_clean();

// Output the generated CSV file.
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=cronnit_export_$accountName.csv");
rewind($output);
fpassthru($output);

// Skip all higher level scripting (page templates etc.).
exit(0);
