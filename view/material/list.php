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
	if ($item['folder']) {
		return 'folder_open';
	}

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
	<ul class="mdui-list" id="file-list" v-cloak>
		<li class="mdui-list-item th">
		  <div class="mdui-col-xs-12 mdui-col-sm-7" @click.prevent="sort('name')">
				文件 <i v-if="sortType==='name'" class="mdui-icon material-icons">expand_{{ sortAsc ? 'less' : 'more'}}</i>
			</div>
		  <div class="mdui-col-sm-3 mdui-text-right" @click.prevent="sort('lastModifiedDateTime')">
				修改时间 <i v-if="sortType==='lastModifiedDateTime'" class="mdui-icon material-icons">expand_{{ sortAsc ? 'less' : 'more'}}</i>
			</div>
		  <div class="mdui-col-sm-2 mdui-text-right" @click.prevent="sort('size')">
				大小 <i v-if="sortType==='size'" class="mdui-icon material-icons">expand_{{ sortAsc ? 'less' : 'more'}}</i>
			</div>
		</li>
		<?php if($path != '/'):?>
		<li class="mdui-list-item mdui-ripple">
			<a href="<?php echo get_absolute_path($root.$path.'../');?>">
			  <div class="mdui-col-xs-12 mdui-col-sm-7">
				<i class="file-icon mdui-icon material-icons">arrow_upward</i>
		    	..
			  </div>
			</a>
		</li>
		<?php endif;?>

		<li v-for="item in listToShow"
			:key="item.name"
			class="mdui-list-item mdui-ripple"
			:class="item.folder ? 'folder' : 'file'"
			@click.stop="view(item, $event)"
		>
			<div class="mdui-col-xs-12 mdui-col-sm-7 mdui-text-truncate mdui-valign">
				<i class="file-icon mdui-icon material-icons">{{ item.iconType }}</i>
				<a :href="item.viewLink">
					{{ item.name }}
				</a>
				<a v-if="!item.folder" class="dl-link" :href="item.downloadLink">
					<i class="mdui-icon material-icons">file_download</i>
				</a>
			</div>
			<div class="mdui-col-sm-3 mdui-text-right">{{ item.dateTimeStr }}</div>
			<div class="mdui-col-sm-2 mdui-text-right">{{ item.fileSizeStr }}</div>
		</li>
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

new Vue({
	el: '#file-list',
	data() {
		return {
			list: [
				<?php foreach($items as $item) {
					unset($item['downloadUrl']);
					$item['dateTimeStr'] = date("Y-m-d H:i:s", $item['lastModifiedDateTime']);
					$item['fileSizeStr'] = onedrive::human_filesize($item['size']);
					$item['iconType'] = file_ico($item);
					$item['viewLink'] = $file_link($item);
					$item['downloadLink'] = $file_link($item, null);
					$item['thumbUrl'] = $file_link($item, 't');
					echo json_encode($item);
					echo ',';
				} ?>
			],
			sortType: null,
			sortAsc: true,
		}
	},
	methods: {
		view(item, e) {
			let target = e.target;
			while (target !== e.currentTarget) {
				if (target.tagName === 'A') {
					return;
				}

				target = target.parentElement;
			}

			const link = item.viewLink;
			if (e.ctrlKey) {
				window.open(link);
			} else {
				window.location = link;
			}
		},
		sort(type) {
			if (type !== this.sortType) {
				this.sortType = type;
				this.sortAsc = true;
			} else if (this.sortAsc) {
				this.sortAsc = false;
			} else {
				this.sortType = null;
				this.listToShow = this.list;
			}
		}
	},
	computed: {
		listToShow() {
			const type = this.sortType;
			if (!type) {
				return this.list;
			}

			const sortAsc = this.sortAsc;
			const tmpArray = [...this.list];
			tmpArray.sort((a, b) => {
				let av = a[type], bv = b[type];
				if (typeof av !== 'string') {
					av = av.toString();
					bv = bv.toString();
				}
				const v = av.localeCompare(bv, undefined, { numeric: true });
				return sortAsc ? v : 0 - v;
			});

			return tmpArray;
		}
	}
})
</script>
<?php view::end('content');?>
