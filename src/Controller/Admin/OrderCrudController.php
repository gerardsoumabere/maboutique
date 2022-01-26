<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private CrudUrlGenerator $crudUrlGenerator;
    private AdminUrlGenerator $adminUrlGenerator;

//    public function __construct(EntityManagerInterface $entityManager,CrudUrlGenerator $crudUrlGenerator,AdminUrlGenerator $adminUrlGenerator) Ancienne version
    public function __construct(EntityManagerInterface $entityManager,AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
//        $this->crudUrlGenerator = $crudUrlGenerator; ancienne version
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {

        $updatePreparation = Action::new('updatePreparation','Préparation en cours','fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery','Livraison en cours','fas fa-truck')->linkToCrudAction('updateDelivery');
        return $actions
            ->add('index', 'detail')
            ->add('detail',$updatePreparation)
            ->add('detail',$updateDelivery);
    }

    public function updatePreparation(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order ->setState(2);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:green;'<strong>La commande ".$order->getReference()." est bien <u>en cours de préparation.</u></strong></span>");

        $url = $this->adminUrlGenerator
            ->setController((OrderCrudController::class))
            ->setAction(ACTION::INDEX)
            ->generateUrl();

        $mail = new Mail();
        $content = "Bonjour".$order->getUser()->getFirstname()."<br/> Votre commande Maboutique est en cours de preparation.";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande Maboutique est en cours de preparation.',$content);

        return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order ->setState(3);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:orange;'<strong>La commande ".$order->getReference()." est bien <u>en cours de livraison.</u></strong></span>");

        $url = $this->adminUrlGenerator
            ->setController((OrderCrudController::class))
            ->setAction(ACTION::INDEX)
            ->generateUrl();

        $mail = new Mail();
        $content = "Bonjour".$order->getUser()->getFirstname()."<br/> Votre commande Maboutique est en cours de livraison.";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande Maboutique est en cours de livraison.',$content);

        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=> 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('createdAt','Passée le'),
            TextField::new('user.getFullname','Utilisateur'),
            TextEditorField::new('delivery','Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('total','Total produit')->setCurrency('EUR'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            TextField::new('carrierName','Transporteur'),
            ChoiceField::new('state')->setChoices([
                'Non payée'=> 0,
                'Payée'=> 1,
                'Préparation en cours'=> 2,
                'Livraison en cours'=> 3

            ]),
            ArrayField::new('orderDetails','Produits achetés')->hideOnIndex()
        ];
    }
}
