<ul class="nav nav-pills nav-stacked">
    <li><a class="list-group-item <? if($this->mod=='moduleconfig'){print 'active';}?>" onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=configuration&mod=moduleconfig"><?=$this->LANG['moduleConfigurate']?></a></li>
    <li><a class="list-group-item <? if($this->mod=='newConfig'){print 'active';}?>" onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=configuration&mod=newConfig"><?=$this->LANG['idConfigurate']?></a></li>
</ul>
