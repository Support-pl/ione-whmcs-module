<ul class="nav nav-pills nav-stacked" onclick="$('.loading').attr('hidden',false);">
    <li><a class="list-group-item <?php if($this->mod=='vmlist'){print 'active';}?>" onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=vmlist&mod=vmlist"><?=$this->LANG['usedIP']?></a></li>
    <li><a class="list-group-item <?php if($this->mod=='qutas'){print 'active';}?>" onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=vmlist&mod=qutas"><?=$this->LANG['quotas']?></a></li>
</ul>
