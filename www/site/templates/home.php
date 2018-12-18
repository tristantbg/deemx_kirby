<?php snippet('header') ?>

ok home

<?php var_dump($data) ?>

<?php
  foreach($data['items'] as $item) {
    echo $item->id."<br />";
    echo $item->title . "<br />";
    echo $item->content . "<br />";
    echo $item->published_at . "<br />";
    echo $item->created_at . "<br />";
    echo $item->updated_at . "<br />";
    echo $item->slug . "<br />";
    echo $item->tag_names . "<br />";
    echo $item->time . "<br />";
    echo $item->price . "<br />";
    echo $item->end_at . "<br />";
    echo $item->start_at . "<br />";
    echo $item->subtitle . "<br />";
    echo $item->marquee_text . "<br />";
    echo $item->vernissage_date . "<br />";
    echo $item->vernissage_time . "<br />";
    echo "<hr />";
    echo "<a href='<?php echo '/event/'.$item->slug; ?>Go to post</a>";
    echo "<hr />";
  }
?>

<?php if ($nextPage): ?>
<a href="<?= $page->url()."/page:".$nextPage ?>">Next page</a>
<?php endif ?>

<?php snippet('footer') ?>
