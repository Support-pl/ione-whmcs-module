<?php
use WHMCS\Database\Capsule;
class Loader{
    protected $vars;
    protected $LANG;
    protected $tabs;
    protected $mod;
    private $onconnect;
    private $modulelink;

    public function __construct($vars)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/servers/onconnector/lib/ONConnect.php');
        $this->onconnect = new ONConnect();
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $this->vars = $vars;
        $this->LANG = $this->vars['_lang'];
        $this->tabs = $_GET['tabs'];
        $this->mod = $_GET['mod'];
        $pageUrl['mod'] = $this->mod;
        $pageUrl['tabs'] = $this->tabs;
        $pageUrl = http_build_query($pageUrl);
				$this->modulelink=$vars['modulelink'].'&'.$pageUrl;
    }

    private function addOrUpdate($name,$value){
        if(Capsule::table( 'tblconfiguration' )
            ->where('setting',$name)
            ->get()) {
            Capsule::table( 'tblconfiguration' )
                ->where('setting',$name)
                ->update([
                    'setting'=>$name,
                    'value'=>$value
                ]);
        }
        else{
            $result = Capsule::table('tblconfiguration')
                ->insert([
                    'setting' => $name,
                    'value' => $value
                ]);
        };
    }
    private function loadTabs()
    {
        ?>
            <div class="nav-menu">
                  <ul class="nav nav-tabs">
                      <li class=" <?php if($this->tabs=='vmlist') {print 'active';};?>"><a onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=vmlist"><span class="glyphicon glyphicon-th-list"></span> <?=$this->LANG['tabsvmlist']?></a></li>
                      <li class=" <?php if($this->tabs=='configuration') {print 'active';};?>"><a onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>&tabs=configuration"><span class="glyphicon glyphicon-cog"></span> <?=$this->LANG['tabsconfiguration']?></a></li>
                  </ul>
            </div>
        <?php
    }

    private function breadCrumbs()
    {
        ?>
           <div>
                  <ul class="breadcrumb" onclick="$('.loading').attr('hidden',false);">
                      <li><a onclick="$('.loading').attr('hidden',false);" href="<?=$this->vars['modulelink']?>"><span class="glyphicon glyphicon-home"></span></a> <span class="divider">/</span></li>
                      <?php if($this->tabs){ printf('<li><a onclick="$(\'.loading\').attr(\'hidden\',false);" href="%s&tabs=%s">%s</a> <span class="divider">/</span></li>',$this->vars['modulelink'],$this->tabs,$this->LANG['tabs'.$this->tabs]);}?>
                      <?php if($this->mod){ printf('<li><a onclick="$(\'.loading\').attr(\'hidden\',false);" href="%s&tabs=%s&mod=%s">%s</a> <span class="divider">/</span></li>',$this->vars['modulelink'],$this->tabs,$this->mod,$this->mod);}?>
                  </ul>
           </div>
        <?php
    }

    public function constructPage()
    {
        if ($this->tabs == NULL) {
        $this->tabs = 'vmlist';
        $this->mod = 'vmlist';
        }?>
        <?=$this->loadTabs();?>
        <?=$this->breadCrumbs();?>

        <div>
            <div class="col-lg-2">
                <?=$this->loadPageMenu();?>
            </div>

            <div class="col-lg-10">
                <?=$this->loadPageContent();?>
            </div>
        </div>
        <div hidden class="loading" style="position: absolute; z-index: 1000000;top:200px;left:47%;">
            <img src="/modules/addons/oncontrol/img/loading.gif">
        </div>
    <?php }

    public function constructPageFirstStart()
    {
            $this->tabs = 'configuration';
            $this->mod = 'moduleconfig';
        ?>
        <?=$this->loadTabs();?>
        <?=$this->breadCrumbs();?>

        <div>
            <div class="col-lg-2">
                <?=$this->loadPageMenu();?>
            </div>

            <div class="col-lg-10">
                <?=$this->loadPageContent();?>
            </div>
        </div>
    <?php }


    public function loadPageMenu()
    {
        if($this->tabs) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/addons/oncontrol/pages/' . $this->tabs . '/menu.php');
        }
    }

    public function loadPageContent()
    {
        if($this->mod) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/modules/addons/oncontrol/pages/' . $this->tabs . '/' . $this->mod . '.php');
        }
    }

}