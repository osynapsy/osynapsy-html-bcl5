<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Bcl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component\Base;
use Osynapsy\Html\Component\Input;
use Osynapsy\Html\Component\Hidden;
use Osynapsy\Html\DOM;

class FileBox extends Base
{
    protected $fileBox;
    protected $deleteCommand;
    public $showImage = false;
    protected $previewBox;
    protected $labelBox;
    protected $hiddenBox;

    public function __construct($name, $showSendButton = true, $showImagePreview = false)
    {        
        DOM::requireJs('bcl/filebox/script.js');
        parent::__construct('div', $name.'_container');
        $this->addClass('bcl-filebox');
        $this->add($this->hiddenFactory($name));
        if ($showImagePreview) {
            $this->add($this->previewFactory($name));
        }
        $this->add($this->inputGroupFactory($name, $showSendButton));        
    }
    
    protected function hiddenFactory($name)
    {
        return $this->hiddenBox = new Hidden($name);        
    }
    
    protected function previewFactory($name)
    {
        return $this->previewBox = new Tag('div', $name.'_preview','bcl-filebox-preview pb-1');
    }
    
    protected function inputGroupFactory($name, $showSendButton)
    {
        $inputGroup = new Tag('div', null, 'input-group');                
        $inputGroup->add($this->buttonBrowseFactory($name));
        $inputGroup->add($this->fileBoxFactory($name));
        $inputGroup->add($this->fieldLabelFileBoxFactory());
        if ($showSendButton) {
            $inputGroup->add($this->buttonSendFileFactory($name, $showSendButton));
        }
        return $inputGroup;
    }
    
    protected function buttonBrowseFactory($componentId)
    {
        $Button = new Tag('label', null, 'input-group-text btn btn-outline-primary btn-file');
        $Button->attribute('for', $componentId . '_file');
        $Button->add('...');
        return $Button;
    }
    
    protected function fileBoxFactory($name)
    {
        $id = $name . '_file';
        $FileBox = new Input($id, $id, 'file');
        return $FileBox->addClass('d-none');
    }
    
    protected function fieldLabelFileBoxFactory()
    {
        $this->labelBox = new TextBox(false);
        $this->labelBox->setReadOnly(true);
        return $this->labelBox;
    }
    
    protected function buttonSendFileFactory($name, $label)
    {        
        $Button = new Button($name.'-send', $label === true ? 'Invia' : $label, 'btn-outline-primary bcl-filebox-send');        
        $Button->setAction('upload', [$name]);
        return $Button;
    }

    public function preBuild()
    {
        if (empty($_REQUEST[$this->id])) {
            return;
        }
        $this->downloadFileFactory();
    }

    protected function downloadFileFactory()
    {
        $pathinfo = pathinfo($_REQUEST[$this->id]);
        $filename = $pathinfo['filename'].(!empty($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '');
        $download = new Tag('a');
        $download->att('target','_blank')->att('href',$_REQUEST[$this->id])->add($filename.' <span class="fa fa-download"></span>');
        $label = $this->span->add(new LabelBox('donwload_'.$this->id));
        $label->att('style','padding: 10px; background-color: #ddd; margin-bottom: 10px;');
        $label->setLabel($download, $this->deleteCommand);
        $this->span->add($label);
    }

    public function setDeleteAction($action, $parameters = [], $confirmMessage = null)
    {
        $button = new Tag('span', null, 'fa fa-close click-execute float-right');
        $button->att('data-action', $action);
        if (!empty($parameters)) {
            $button->att('data-action-parameters', implode(',', $parameters));
        }
        if (!empty($confirmMessage)) {
            $button->att('data-confirm', $confirmMessage);
        }
        $this->deleteCommand = $button;
    }
    
    public function setValue($value)
    {        
        $this->hiddenBox->setValue($value);        
        if ($this->previewBox) {
            $this->previewBox->add(sprintf('<img src="%s" style="max-width: 100%%">', $value));
        }
        return $this;
    }
}