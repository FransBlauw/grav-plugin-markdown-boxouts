<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class MarkdownBoxoutsPlugin extends Plugin
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0],
            'onTwigSiteVariables'   => ['onTwigSiteVariables', 0]
        ];
    }

    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event['markdown'];

        $markdown->addBlockType('!', 'Boxout', true, false);

        $markdown->blockBoxout = function($Line) {
			
            $ConfigClasses = $this->config->get('plugins.markdown-boxouts.boxout_classes');
			$Classes = array();
			foreach($ConfigClasses as $Class) {
				if (!isset($Class['classname'])) continue;
				$Classes[$Class['classname']] = $Class;
			}
			
			if (empty($Classes)) return;
			
            if (preg_match('/^(!)(\(([\w-_]*)\))[ ]+(.*)/', $Line['text'], $matches))
            {
				$classname = $matches[3];
				if (!isset($Classes[$classname])) return;
				$BoxoutTitle = $Classes[$classname]['heading'];
				$BoxoutIcon = $Classes[$classname]['fontawesome'];
				
                $text = $matches[4];
	
				$BodyElement = array(
					'name' => 'div',
					'attributes' => array(
                            'class' => 'boxout-body',
                    ),
					'handler' => 'lines',
					'text' => array($text),
				);
				
				$HeaderElement = array(
					'name' => 'div',
					'attributes' => array(
                            'class' => 'boxout-header',
                    ),
					'text' => '<i class="fa fa-fw '.$BoxoutIcon.'"></i> '.$BoxoutTitle,
				);
				
				$BoxoutElements[] = $HeaderElement;
				$BoxoutElements[] = $BodyElement;
				
				$Block = array(
					'element' => array(
						'name' => 'div',
						'attributes' => array(
                            'class' => 'boxout '.$classname,
						),
						'handler' => 'elements',
						'text' => array($HeaderElement,$BodyElement),
					),
				);		

                return $Block;
			};
			
        };

        $markdown->blockBoxoutContinue = function($Line, array $Block) {

            if (isset($Block['interrupted']))
            {
                return;
            }

            if ($Line['text'][0] === '!' and preg_match('/^(!)(.*)/', $Line['text'], $matches))
            {
                $Block['element']['text'][1]['text'][]= ltrim($matches[2]);
                return $Block;
            }
        };
		
		$markdown->blockBoxoutComplete = function($Block)
		{
			return $Block;
		};
    }

    public function onTwigSiteVariables()
    {
        if ($this->config->get('plugins.markdown-boxouts.built_in_css')) {
            $this->grav['assets']
                ->add('plugin://markdown-boxouts/assets/boxouts.css');
        }
    }

}