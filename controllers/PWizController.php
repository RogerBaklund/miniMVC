<?php
include 'DefaultController.php';

class PWizController extends DefaultController {
  
  const APP_NAME = 'Project Wizard',
        APP_VERSION = '0.1a';
  
  function __construct($path) {
    parent::__construct($path);
    View::setDefaultPath('views/pwiz/');
    $this->extra_style = '';
  }
  
  function _Default($params) {
    if(!$this->action) {
      $msg = ($this->request_method == 'POST') ? $this->createProject() : '';
      $content = 
          $this->pageHeader().
          $this->pageInfo($msg).
          $this->mainForm();
      $this->ShowPage($content,$this->extra_style);
    } else parent::_Default(); // error
  }
  
  protected function pageHeader() {
    return '<h3>'.self::APP_NAME.' <span style="font-size:65%;font-weight:normal;color:silver;">v'.self::APP_VERSION.'</span></h3>';
  }
  protected function pageInfo($msg='') {
    return new View('info',array('msg'=>$msg));
  }
  protected function mainForm() {
    require_once 'FormBuilder.php';
    
    FormBuilder::$form_content_class = 'projectform';

    $style = array(
      'width'=>'22em',
      'background'=>'#eeff77');
    $this->extra_style = FormBuilder::makeCSS($style).
      '.projectform {border:solid 1px black;}'.
      '.projectform p {margin:.5em 0;}'.
      '.projectform label {color:blue}';
        
    $inline = array('*label_style'=>'width:7em','*wrapper'=>'<div style="display:inline-block">%s</div>');
    
    $CDN_file = new JSONModel('json/CDN_library.json');
    $CDN = array();
    if($CDN_file->isLoaded()) {
      $CDN['*CDN_lib'] = 
          '<p>Enable CDN based libraries. '. 
          'These will be inserted in the head part of the output.</p>';
      $CDN['*cdn-fs-start'] = '<fieldset><legend>CDN</legend>';
      foreach($CDN_file->List as $i => $entry) 
        $CDN['cdn_'.$i] = array('checkbox',0,$entry['Name'],$inline);
      $CDN['*cdn-fs-end']= '</fieldset>';
    }
    
    $form = array(
      '*header' => '<h2>Create project</h2>',
      '*project_name' => '<p>What will be the full name of the project? This name may contain spaces.</p>',
      'project_name' => array('varchar(80)','',NULL,'autofocus'),
      '*short_name' => '<p>Enter a short version of the name, this will be the name of the directory, no spaces.</p>',
      'short_name' => array('varchar(15)','',NULL,array('style'=>'width:6em;')),
      '*author' => '<p>Provide the name of the author or organization for copyright notice.</p>',
      'author' => array('varchar(40)'),
      '*views' => '<p>What kind of views do you want for this project?</p>',
      'views' => array("enum('None','Single page','Page/head/body')",3,NULL,array('*enum_type'=>'dropdown')),
      '*controllers'=>'<p>Are you going to use controllers?</p>',
      'controllers'=>array("radio('Yes','No')",1),
      # doctype (or just html/xhtml?)
      # stylesheet
      # javascript
      ) + $CDN + array(
      '*fs1' => '<p>Check the boxes below to indicate which features you will need for this project.</p>',
      '*fs1-start' => '<fieldset><legend>Features</legend>',
      'dbmodel' => array('checkbox',0,'DBModel',$inline),
      'formbuilder' => array('checkbox',0,'FormBuilder',$inline),
      'simplemenu' => array('checkbox',0,'SimpleMenu',$inline),
      'simpleinput' => array('checkbox',0,'SimpleInput',$inline),
      'bootstrapui' => array('checkbox',0,'BootstrapUI',$inline),      
      '*fs1-end' => '</fieldset>',      
      'save' => array('submit','Create'),
    );
    
    // If this is a submit, fill out form with provided values
    if(count($_POST))
      FormBuilder::fillForm($form,$_POST,'trim');
    
    // Place cursor in the field with error (html5)
    if(isset($this->error_field)) {
      $form['project_name'][3] = ''; # remove default autofocus
      $attr = isset($form[$this->error_field][3]) ? $form[$this->error_field][3] : '';
      if(is_string($attr)) 
        $attr = $attr ? "$attr autofocus" : 'autofocus';
      else
        $attr['autofocus'] = 'autofocus';
      $form[$this->error_field][3] = $attr;
    }
    
    // Return the form as a string
    return FormBuilder::makeForm($form);
  }
  
  protected function fieldError($fieldname,$msg) {
    $this->error_field = $fieldname;
    return error($msg);
  }
  
  protected function createProject() {
    $m = new Model($_POST);
    # validate
    # 1) check if required input i provided
    if(!trim($m->project_name))
      return error('Project name must be provided');
    if(!trim($m->short_name)) 
      return $this->fieldError('short_name','Short name must be provided');
    if(strpos($m->short_name,' ') !== false)
      return $this->fieldError('short_name','Short name can not have spaces');
    $docroot = $_SERVER['DOCUMENT_ROOT'];
    $new_dir = $docroot.'/'.$m->short_name;
    # 2) check if folder already exists
    if(is_dir($new_dir))
      return $this->fieldError('short_name','Directory '.$new_dir.' already exists');
    # check if we have permission
    if(!mkdir($new_dir))
      return $this->fieldError('short_name','Failed creating directory '.$newdir.', check permissions');
    # create
    copy('miniMVC.php',$new_dir.'/miniMVC.php');
    copy('.htaccess',$new_dir.'/.htaccess');
    if(isset($m->dbmodel)) 
      copy('DBModel.php',$new_dir.'/DBModel.php');
    if(isset($m->formbuilder)) 
      copy('FormBuilder.php',$new_dir.'/FormBuilder.php');
    if(isset($m->simplemenu)) 
      copy('SimpleMenu.php',$new_dir.'/SimpleMenu.php');
    if(isset($m->simpleinput)) 
      copy('SimpleInput.php',$new_dir.'/SimpleInput.php');
    if(isset($m->bootstrapui)) 
      copy('BootstrapUI.php',$new_dir.'/BootstrapUI.php');
    $m->timestamp = date('Y-m-d H:i:s');
    $m->appname = self::APP_NAME;
    $m->appversion = self::APP_VERSION;
    $m->frontpage_content = "<p style=\"color:green\">Hello world!</p>";
    
    $TestCode = '';
    $m->external_files = '';
    $CDN_file = new JSONModel('json/CDN_library.json');
    $JS = new FormatView('<script type="text/javascript" src="%s"></script>'."\n");
    if($CDN_file->isLoaded()) {
      foreach($_POST as $k=>$v) {
        if(substr($k,0,4) == 'cdn_') { 
          $idx = substr($k,4);
          $m->external_files .= $JS->render($CDN_file->List[$idx]['CDN']);
          #$m->external_files .= '<script type="text/javascript" src="'.
          #                 $CDN_file->List[$idx]['CDN'].'"></script>'."\n";
          $TestCode .= $CDN_file->List[$idx]['TestCode']."\n";
        }
      }
    }
    if($TestCode)
      $m->frontpage_content = $TestCode;
    $m->menu_code = new View('templates/',$m,'menu_code.tpl');
    $m->view_code = new View('templates/',$m,'view_code.tpl');
    $m->index_includes = '';
    $m->controller_includes = '';
    $this->setIncludes($m);
    
    if($m->controllers == 1) {
      mkdir($new_dir.'/controllers');
      $tpl = new View('templates/',$m,'DefaultController.tpl');
      file_put_contents($new_dir.'/controllers/DefaultController.php',"<?php\n".$tpl);
    }
    
    $tpl = new View('templates/',$m,'index.tpl');
    file_put_contents($new_dir.'/index.php',"<?php\n".$tpl);
    if($m->views > 1) {
      mkdir($new_dir.'/views');
      file_put_contents($new_dir.'/views/frontpage.php',$m->frontpage_content);
      copy('views/pwiz/templates/page_template.tpl',$new_dir.'/views/page_template.php');
      if($m->views == 2) {}
      elseif($m->views == 3) {
        copy('views/pwiz/templates/head_template.tpl',$new_dir.'/views/head_template.php');
        copy('views/pwiz/templates/body_template.tpl',$new_dir.'/views/body_template.php');
      }
    }
    return '<p id="msg" style="color:green">Project created! '.
      '<a href="/'.$m->short_name.'">Goto project</a></p>'.
      '<script>'.
      "setTimeout(function(){document.getElementById('msg').style.color='red';},500);".
      "setTimeout(function(){document.getElementById('msg').style.color='green';},2000);".
      '</script>';
  }
  private function setIncludes(&$m) {
    $inc = ($m->controllers == 1) ? 'controller_includes':'index_includes';
    if(isset($m->database)) $m->$inc .= "include 'DBModel.php';\n";
    if(isset($m->formbuilder)) $m->$inc .= "include 'FormBuilder.php';\n";
    if(isset($m->simplemenu)) $m->$inc .= "include 'SimpleMenu.php';\n";
    if(isset($m->simpleinput)) $m->$inc .= "include 'SimpleInput.php';\n";
    if(isset($m->bootstrapui)) $m->$inc .= "include 'BootstrapUI.php';\n";
  }
}
