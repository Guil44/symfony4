<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerOnix2Qp\srcApp_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerOnix2Qp/srcApp_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerOnix2Qp.legacy');

    return;
}

if (!\class_exists(srcApp_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerOnix2Qp\srcApp_KernelDevDebugContainer::class, srcApp_KernelDevDebugContainer::class, false);
}

return new \ContainerOnix2Qp\srcApp_KernelDevDebugContainer([
    'container.build_hash' => 'Onix2Qp',
    'container.build_id' => 'a75ff1a1',
    'container.build_time' => 1574693707,
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerOnix2Qp');
