<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Controllers;

abstract class AbstractAdminController extends AbstractController
{
    /**
     * @var string list template path.
     */
    protected string $listTemplate = 'admin.list';

    /**
     * @var string show template path.
     */
    protected string $showTemplate = 'admin.show';

    /**
     * @var string create form template path.
     */
    protected string $createTemplate = 'admin.create';

    /**
     * @var string edit form template path.
     */
    protected string $editTemplate = 'admin.edit';
}
