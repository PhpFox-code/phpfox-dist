<?php

namespace Core;

class View {

	public static $template = 'layout';

	private $_loader;
	private $_env;
	private $_render = [];

	public function __construct() {
		$this->_loader = new View\Loader();
		$this->_loader->addPath(PHPFOX_DIR . 'theme/default/html', 'Theme');
		$this->_loader->addPath(PHPFOX_DIR . 'views', 'Base');

		$this->_env = new \Twig_Environment($this->_loader, array(
			'cache' => false,
			'autoescape' => false
		));

		$this->_env->setBaseTemplateClass('Core\View\Base');

		$this->_env->addFunction(new \Twig_SimpleFunction('url', function($url, $params = []) {
			return \Phpfox_Url::instance()->makeUrl($url, $params);
		}));

		$this->_env->addFunction(new \Twig_SimpleFunction('phrase', function() {
			return call_user_func_array('phrase', func_get_args());
		}));

		$this->_env->addFunction(new \Twig_SimpleFunction('_p', function() {
			return call_user_func_array('_p', func_get_args());
		}));
	}

	public function loader() {
		return $this->_loader;
	}

	public function render($name, array $params = []) {
		$this->_render = [
			'name' => $name,
			'params' => $params
		];

		return $this;
	}

	public function getContent() {
		$Template = \Phpfox_Template::instance();
		if (!$this->_render) {

			$this->_render = [
				'name' => '@Base/' . self::$template . '.html',
				'params' => [
					'content' => new View\Functions('content'),
					'top' => new View\Functions('top'),
					'breadcrumb' => new View\Functions('breadcrumb'),
					'title' => new View\Functions('title'),
					'h1' => new View\Functions('h1'),
					'errors' => new View\Functions('errors')
				]
			];

		}

		$params = $this->_render['params'];
		$params['header'] = $Template->getHeader();
		$params['js'] = $Template->getFooter();
		$params['nav'] = new View\Functions('nav');
		$params['footer'] = new View\Functions('footer');
		$params['notification'] = new View\Functions('notification');
		$params['logo'] = new View\Functions('logo');
		$params['body'] = 'id="page_' . \Phpfox_Module::instance()->getPageId() . '" class="' . \Phpfox_Module::instance()->getPageClass() . '"';

		$locale = \Phpfox_Locale::instance()->getLang();
		$params['html'] = 'xmlns="http://www.w3.org/1999/xhtml" dir="' . $locale['direction'] . '" lang="' . $locale['language_code'] . '"';

		return $this->_env->render($this->_render['name'], $params);
	}
}