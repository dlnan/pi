<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\View\Resolver;

use Pi;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface;

/**
 * Module template resolver
 *
 * Usage
 *
 *  ```
 *      // Full path
 *      $model->setTemplate('/full/path/to/template.html');
 *      // Relative path with specified module
 *      $model->setTemplate('<module>:<path/to/template>');
 *      // Relative path w/o specified module
 *      $model->setTemplate('path/to/template');
 *  ```
 *
 * Look up in module template folders
 *
 * - Module custom templates in a theme:
 *   - for module "demo":
 *      `theme/default/module/demo/template/[front/template.html]`
 *   - for module "democlone":
 *      'theme/default/module/democlone/template/[front/template.html]`
 *
 * - Module custom templates:
 *   - for module "demo":
 *      `custom/demo/template/[front/template.html]`
 *   - for module "democlone":
 *      'custom/democlone/template/[front/template.html]`
 *
 * - Module native templates:
 *   - for both module "demo" and cloned "democlone":
 *      `module/demo/template/[front/template.html]`
 *
 * @see    Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see    Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @see    Pi\Application\Service\Asset for asset skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleTemplate implements ResolverInterface
{
    /**
     * Theme template directory
     *
     * @var string
     */
    protected $templateDirectory = 'template';

    /**
     * Suffix to use: appends this suffix if the template requested
     * does not use it.
     *
     * @var string
     */
    protected $suffix = 'phtml';

    /**
     * Set default file suffix
     *
     * @param string $suffix
     *
     * @return self
     */
    public function setSuffix($suffix)
    {
        $this->suffix = (string)$suffix;

        return $this;
    }

    /**
     * Get file suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Canonize template
     *
     * @param string $name
     *
     * @return array|string Pair of module and template name,
     *      or full path to template
     */
    protected function canonizeTemplate($name)
    {
        // Empty template
        if ('__NULL__' == $name) {
            return ['system', 'dummy'];
        }
        // With suffix
        if (substr($name, -6) == '.' . $this->suffix) {
            // Full path to template
            if (file_exists($name)) {
                return $name;
            }
            // Remove suffix
            $name = substr($name, 0, -6);
        }
        $segments = explode(':', $name, 2);
        if (isset($segments[1])) {
            [$module, $template] = $segments;
        /*
        if ('module/' == substr($module, 0, 7)) {
            $module = substr($module, 7);
        }
        */
        } else {
            $module   = Pi::service('module')->current();
            $template = $name;
        }

        return [$module, $template];
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @FIXME Is performance a problem?
     *
     * @param string        $name       Relative or full path to template,
     *                                  it is highly recommended to remove suffix from relative template
     * @param null|Renderer $renderer
     * @param bool          $forcefront force to find template in the front theme
     *
     * @return string|false
     */
    public function resolve($name, Renderer $renderer = null, $forcefront = false)
    {
        // Set template context
        $renderer->context('module');
        $return = $this->canonizeTemplate($name);
        if (!is_array($return)) {
            return $return;
        }
        [$module, $template] = $return;
        // Check custom template in theme
        $path = sprintf(
            '%s/%s/module/%s/%s/%s.%s',
            Pi::path('theme'),
            $forcefront ? Pi::config('theme') : Pi::service('theme')->current(),
            $module,
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }
        // Check custom template in module custom path
        $path = sprintf(
            '%s/module/%s/%s/%s.%s',
            Pi::path('custom'),
            $module,
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }
        // Check local template in module
        $path = sprintf(
            '%s/%s/%s/%s.%s',
            Pi::path('module'),
            Pi::service('module')->directory($module),
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }

        // Reset template context
        $renderer->context('');

        return false;
    }
}
