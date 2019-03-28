<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Form\Extension;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserExtension extends AbstractTypeExtension
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function getExtendedType(): string
    {
        // BC symfony <4.2
        return FormType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('user_mapping', []);
        $resolver->setAllowedTypes('user_mapping', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($mapping = $options['user_mapping']) {
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($mapping): void {
                $data = $event->getData();
                foreach ($mapping as $sourceField => $targetField) {
                    if (!isset($data[$sourceField])) {
                        continue;
                    }
                    try {
                        $data[$targetField] = $this->repository->findByUsername($data[$sourceField]);
                    } catch (EntityNotFoundException $e) {
                        $data[$targetField] = null;
                    }
                }
                $event->setData($data);
            });
        }
    }
}
