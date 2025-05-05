<?php

namespace App\Custom\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class ResourceRegistrar extends OriginalRegistrar
{
    // add data to the array
    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = ['list', 'index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'modal', 'pdf', 'ajax', 'importar', 'filter', 'copy'];

    protected function addResourceModal($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/modal';

        $action = $this->getResourceAction($name, $controller, 'modal', $options);

        return $this->router->get($uri, $action);
    }

    protected function addResourcePdf($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/pdf';

        $action = $this->getResourceAction($name, $controller, 'pdf', $options);

        return $this->router->get($uri, $action);
    }

    protected function addResourceAjax($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/ajax';

        $action = $this->getResourceAction($name, $controller, 'ajax', $options);

        return $this->router->get($uri, $action);
    }

    protected function addResourceCopy($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/copy';

        $action = $this->getResourceAction($name, $controller, 'copy', $options);

        return $this->router->get($uri, $action);
    }

    protected function addResourceImportar($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'importar', $options);

        return $this->router->post($uri, $action);
    }

    protected function addResourceFilter($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'filter', $options);

        return $this->router->post($uri, $action);
    }

    protected function addResourceList($name, $base, $controller, $options)
    {
        $uri    = $this->getResourceUri($name).'/list';
        $action = $this->getResourceAction($name, $controller, 'list', $options);
        return $this->router->get($uri, $action);
    }

    protected function addResourceCreate($name, $base, $controller, $options)
    {
        unset($options['missing']);

        $uri    = $this->getResourceUri($name).'/create';
        $action = $this->getResourceAction($name, $controller, 'create', $options);
        $this->router->get($uri, $action);

        $uri    = $this->getResourceUri($name).'/create/modal';
        $action = $this->getResourceAction($name, $controller, 'create', $options);
        return $this->router->get($uri, $action);
    }
}
