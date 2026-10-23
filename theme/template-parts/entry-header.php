<?php
/**
 * 文章／頁面的標頭：麵包屑、標題、作者與日期、操作按鈕。
 *
 * @package myLab
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="entry-header">
	<div class="container">
		<?php mylab_breadcrumb(); ?>
		<?php the_title( '<h1 id="post-title" class="entry-title">', '</h1>' ); ?>
		<div class="entry-meta">
			<?php mylab_entry_byline(); ?>
			<?php mylab_entry_actions(); ?>
		</div>
	</div>
</header>
