<?php

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Common\Uri;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Newsletter\Newsletter as CustomNewsletter;

/**
 * Class NewsletterPlugin
 */
class NewsletterPlugin extends Plugin
{
    /**
     * @var CustomNewsletter
     */
    protected $newsletter;

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100001],
                ['setup', 100000],
                ['onPluginsInitialized', 1000]
            ],
            'onTask.subscriber.enable' => ['subscriberController', 0],
            'onTask.subscriber.disable' => ['subscriberController', 0],
            'onFormProcessed' => ['createSubscriberController', 0]
        ];
    }

    /**
     * [onPluginsInitialized:1000] Composer autoload.
     *
     * @return ClassLoader
     */
    public function autoload()
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function setup()
    {
        // @TODO create user directory
    }

    /**
     * Plugin initialization
     */
    public function onPluginsInitialized()
    {
        $this->newsletter = new CustomNewsletter($this->grav);

        /** @var Twig $twig */
        $twig = $this->grav['twig'];

        if ($this->isAdmin()) {
            $twig->plugins_hooked_nav = [
                "PLUGIN_NEWSLETTER.MENU_LABEL"  => [
                    'route' => $this->config->get('plugins.newsletter.admin.route'),
                    'icon' => $this->config->get('plugins.newsletter.admin.menu_icon')
                ]
            ];
            $this->enable([
                'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0]
            ]);
        } else {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
    }

    /**
     * @param Event $event
     */
    public function onAdminTwigTemplatePaths(Event $event)
    {
        $event['paths'] = [__DIR__ . '/themes/admin/templates'];
    }

    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];

        $twig->twig_vars['newsletter'] = $this->newsletter;
    }

    public function subscriberController()
    {
        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $task = !empty($_POST['task']) ? $_POST['task'] : $uri->param('task');
        $task = substr($task, strlen('subscriber.'));
        $post = !empty($_POST) ? $_POST : $uri->params(null, true);

        if (method_exists('Grav\Common\Utils', 'getNonce')) {
            if ($task == 'enable') {
                if (!isset($post['login-form-nonce']) || !Utils::verifyNonce($post['login-form-nonce'], 'login-form')) {
                    $this->grav['messages']->add($this->grav['language']->translate('PLUGIN_LOGIN.ACCESS_DENIED'), 'info');
                    $this->authenticated = false;
                    $twig = $this->grav['twig'];
                    $twig->twig_vars['notAuthorized'] = true;
                    return;
                }
            }
        }
        $post['_redirect'] = 'admin/newsletter';
        $controller = new Newsletter\SubscriberController($this->grav, $task, $post);
        $controller->execute();
        $controller->redirect();
    }

    /**
     * Create subscriber
     *
     * @param Event $event
     */
    public function createSubscriberController(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'subscribe':
                $controller = new Newsletter\SubscribeController($this->grav, $action, $form, $params, $this->newsletter);
                $controller->execute();
                break;
        }
    }
}