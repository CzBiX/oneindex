<?php view::layout('layout') ?>
<?php 
$fake_static = $root[strlen($root) - 1] !== '?';

$file_link = function ($item, $type='s') use ($root, $path, $fake_static) {
	if (!empty($item['folder'])) {
		$link = get_absolute_path($root.$path.rawurlencode($item['name']));
	} else {
		$link = get_absolute_path($root.$path).rawurlencode($item['name']);
		if ($type) {
			$link .= ($fake_static ? '?' : '&') . $type;
		}
	}

	return $link;
};

function file_ico($item){
  $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
  if(in_array($ext,['bmp','jpg','jpeg','png','gif'])){
  	return "image";
  }
  if(in_array($ext,['mp4','mkv','webm','avi','mpg', 'mpeg', 'rm', 'rmvb', 'mov', 'wmv', 'mkv', 'asf'])){
  	return "ondemand_video";
  }
  if(in_array($ext,['ogg','mp3','wav'])){
  	return "audiotrack";
  }
  return "insert_drive_file";
}
?>

<?php view::begin('content');?>
	
<div class="mdui-container-fluid">

<?php if($head):?>
<div class="mdui-typo" style="padding: 20px;">
	<?php e($head);?>
</div>
<?php endif;?>

	
<div class="mdui-row">
	<ul class="mdui-list" id="file-list">
		<li class="mdui-list-item th">
		  <div class="mdui-col-xs-12 mdui-col-sm-7">文件 <i class="mdui-icon material-icons icon-sort" data-sort="name" data-order="downward">expand_more</i></div>
		  <div class="mdui-col-sm-3 mdui-text-right">修改时间 <i class="mdui-icon material-icons icon-sort" data-sort="date" data-order="downward">expand_more</i></div>
		  <div class="mdui-col-sm-2 mdui-text-right">大小 <i class="mdui-icon material-icons icon-sort" data-sort="size" data-order="downward">expand_more</i></div>
		</li>
		<?php if($path != '/'):?>
		<li class="mdui-list-item mdui-ripple">
			<a href="<?php echo get_absolute_path($root.$path.'../');?>">
			  <div class="mdui-col-xs-12 mdui-col-sm-7">
				<i class="file-icon mdui-icon material-icons">arrow_upward</i>
		    	..
			  </div>
			  <div class="mdui-col-sm-3 mdui-text-right"></div>
			  <div class="mdui-col-sm-2 mdui-text-right"></div>
		  	</a>
		</li>
		<?php endif;?>

		<?php foreach((array)$items as $item):?>
			<?php if(!empty($item['folder'])):?>

		<li class="mdui-list-item folder mdui-ripple"
			data-sort
			data-sort-name="<?php e($item['name']);?>"
			data-sort-date="<?php echo $item['lastModifiedDateTime'];?>"
			data-sort-size="<?php echo $item['size'];?>"
			data-link="<?php echo $file_link($item);?>"
		>
			<div class="mdui-col-xs-12 mdui-col-sm-7 mdui-text-truncate mdui-valign">
				<i class="file-icon mdui-icon material-icons">folder_open</i>
				<a href="<?php echo $file_link($item);?>">
		    	<?php e($item['name']);?>
				</a>
			</div>
			<div class="mdui-col-sm-3 mdui-text-right"><?php echo date("Y-m-d H:i:s", $item['lastModifiedDateTime']);?></div>
			<div class="mdui-col-sm-2 mdui-text-right"><?php echo onedrive::human_filesize($item['size']);?></div>
		</li>
			<?php else:?>
		<li class="mdui-list-item file mdui-ripple"
			data-sort
			data-sort-name="<?php e($item['name']);?>"
			data-sort-date="<?php echo $item['lastModifiedDateTime'];?>"
			data-sort-size="<?php echo $item['size'];?>"
			data-link="<?php echo $file_link($item);?>"
		>
			<div class="mdui-col-xs-12 mdui-col-sm-7 mdui-text-truncate mdui-valign">
				<?php $ico_type = file_ico($item);
					if ($ico_type === 'image'): ?>
				<img class="thumb"
					src="<?php echo $file_link($item, 't=32|32|1');?>"
					srcset="<?php echo $file_link($item, 't=64|64|1');?> 2x"
				>
				<?php else: ?>
				<i class="file-icon mdui-icon material-icons"><?php echo $ico_type;?></i>
				<?php endif; ?>
				<a href="<?php echo $file_link($item);?>">
					<?php e($item['name']);?>
				</a>
				<a class="dl-link" href="<?php echo $file_link($item, null);?>">
					<i class="mdui-icon material-icons">link</i>
				</a>
			</div>
			<div class="mdui-col-sm-3 mdui-text-right"><?php echo date("Y-m-d H:i:s", $item['lastModifiedDateTime']);?></div>
			<div class="mdui-col-sm-2 mdui-text-right"><?php echo onedrive::human_filesize($item['size']);?></div>
		</li>
			<?php endif;?>
		<?php endforeach;?>
	</ul>
</div>
<?php if($readme):?>
<div class="mdui-typo mdui-shadow-3" style="padding: 20px;margin: 20px 0;">
	<div class="mdui-chip">
	  <span class="mdui-chip-icon"><i class="mdui-icon material-icons">face</i></span>
	  <span class="mdui-chip-title">README.md</span>
	</div>
	<?php e($readme);?>
</div>
<?php endif;?>
</div>
<script>
$ = mdui.JQ;

$.fn.extend({
    sortElements: function (comparator, getSortable) {
        getSortable = getSortable || function () { return this; };

        var placements = this.map(function () {
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );

            return function () {
                parentNode.insertBefore(this, nextSibling);
                parentNode.removeChild(nextSibling);
            };
        });

        return [].sort.call(this, comparator).each(function (i) {
            placements[i].call(getSortable.call(this));
        });
    }
});

$(function () {
	$('#file-list').on('click', 'li.file,li.folder', function (e) {
		let el = e.target;
		if ($(el).parent('a').length) {
			return;
		}

		if (el.tagName !== 'LI') {
			el = $(el).parent('li').get(0);
		}

		const link = el.dataset.link;
		if (e.ctrlKey) {
			window.open(link);
		} else {
			location = link;
		}
		return false;
	});

	$('.icon-sort').on('click', function () {
		var sort_type = $(this).attr("data-sort"), sort_order = $(this).attr("data-order");
		var sort_order_to = (sort_order === "less") ? "more" : "less";

		$('li[data-sort]').sortElements(function (a, b) {
			var data_a = $(a).attr("data-sort-" + sort_type), data_b = $(b).attr("data-sort-" + sort_type);
			var rt = data_a.localeCompare(data_b, undefined, {numeric: true});
			return (sort_order === "less") ? 0-rt : rt;
		});

		$(this).attr("data-order", sort_order_to).text("expand_" + sort_order_to);
	})
});
</script>
<?php view::end('content');?>
