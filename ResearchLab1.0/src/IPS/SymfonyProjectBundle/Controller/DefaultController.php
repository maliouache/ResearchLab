<?php

namespace IPS\SymfonyProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use IPS\SymfonyProjectBundle\Entity\Project;
use IPS\SymfonyProjectBundle\Entity\Section;
use IPS\SymfonyProjectBundle\Entity\Reference;
use IPS\SymfonyProjectBundle\Entity\Work;
use IPS\SymfonyProjectBundle\Entity\Comment;
use IPS\SymfonyProjectBundle\Entity\Task;
use Symfony\Component\HttpFoundation\Request;
use IPS\SymfonyProjectBundle\Form\ReferenceType;
use IPS\SymfonyProjectBundle\Form\WorkType;
use IPS\SymfonyProjectBundle\Form\CommentType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use ResearchLabUserBundle\Entity\User;
use Ob\HighchartsBundle\Highcharts\Highchart;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();
        if (is_object($user) and $user instanceof UserInterface){
            return $this->homeAction();
        }
        return $this->render('IPSSymfonyProjectBundle:Default:index.html.twig');
    }

    public function checkLogin(){
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $myredtasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('iNCHARGE'=>$user->getUsername(),'sTATUT'=>0));
        $var=count($myredtasks);
        $this->get('twig')->addGlobal('var', $var);
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $em = $this->getDoctrine()->getManager();
        $myredtasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('iNCHARGE'=>$user->getUsername(),'sTATUT'=>0));
        $var=count($myredtasks);
        $this->get('twig')->addGlobal('var', $var);
    }

    public function homeAction(){
        $this->checkLogin();
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $updated_tasks=array();
        $mytasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('aDDER'=>$user->getUsername()));
        foreach ($mytasks as $task){
            $works=$em->getRepository('IPSSymfonyProjectBundle:Work')->findBy(array('TASK'=>$task));
            $l=0;
            foreach ($works as $work) {
                if ($work->getSEEN()==0){
                    $l=1;
                }
            }
            $comments=$em->getRepository('IPSSymfonyProjectBundle:Comment')->findBy(array('task'=>$task));
            foreach ($comments as $comment) {
                if ($comment->getSEEN()==0){
                    $l=1;
                }
            }
            if ($l==1) {
                array_push($updated_tasks, $task);
            }
        }
        return $this->render('IPSSymfonyProjectBundle::home.html.twig',
            array('mytasks'  => $updated_tasks)
        );
    }

    public function newprojectAction(){
        $this->checkLogin();
        return $this->render('IPSSymfonyProjectBundle::new_project.html.twig');
    }

    public function saveprojectAction(){
        $this->checkLogin();
        $project=new Project();
        $project->init($_POST["name"],$_POST["importance"],$_POST["deadline"],$_POST["domain"],$_POST["comment"]);
        $em=$this->getDoctrine()->getManager();
        $project->addUser($this->getUser());
        $em->persist($project);
        $em->flush();
        return $this->showprojectsAction();
    }
    public function showsearchAction(Request $request){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        //projects
        $query = $em->createQuery('SELECT u FROM IPS\SymfonyProjectBundle\Entity\Project u WHERE (u.nAME LIKE :name )');
        $query->setParameters(array(
            'name' => '%'.$_GET['search'].'%',
        ));
        $projects = $query->getResult();
        $j=0;
        foreach ($projects as $project){
            $sections=$em->getRepository('IPSSymfonyProjectBundle:Section')->findBy(array('PROJECT'=>$project));
            $i=0;$cumul=0;
            foreach ($sections as $section){
                $cumul=$cumul+$section->getSTATUT();
                $i=$i+1;
            }
            if ($i==0){
                $i=1;
            }
            $projects[$j]->progress=$cumul/$i;
            $j=$j+1;
        }
        //sections
        $query = $em->createQuery('SELECT u FROM IPS\SymfonyProjectBundle\Entity\Section u WHERE (u.nAME LIKE :name )');
        $query->setParameters(array(
            'name' => '%'.$_GET['search'].'%',
        ));
        $sections = $query->getResult();
        $j=0;
        foreach ($sections as $section){
            $tasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('SECTION'=>$section));
            $sections[$j]->tasks=$tasks;
            $j=$j+1;
        }
        //tasks
        $query = $em->createQuery('SELECT u FROM IPS\SymfonyProjectBundle\Entity\Task u WHERE (u.nAME LIKE :name )');
        $query->setParameters(array(
            'name' => '%'.$_GET['search'].'%',
        ));
        $tasks = $query->getResult();
        //works
        $query = $em->createQuery('SELECT u FROM IPS\SymfonyProjectBundle\Entity\Work u WHERE (u.uRL LIKE :name )');
        $query->setParameters(array(
            'name' => '%'.$_GET['search'].'%',
        ));
        $works = $query->getResult();
        //references
        $query = $em->createQuery('SELECT u FROM IPS\SymfonyProjectBundle\Entity\Reference u WHERE (u.uRL LIKE :name )');
        $query->setParameters(array(
            'name' => '%'.$_GET['search'].'%',
        ));
        $references = $query->getResult();

        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::result_search.html.twig',
            array('projects'  => $projects,
                  'sections' => $sections,
                  'tasks' => $tasks,
                  'works' => $works,
                  'references' => $references)
        ); 
    }
    public function checkproject(Project $proj,User $user){
        if ($proj->getUSERS()->contains($user)){
            return true;
        }
        return false;
    }
    public function checkprojectaccess(Project $proj,User $user){
        if (!$proj->getUSERS()->contains($user)){
            throw new AccessDeniedException('This user does not have access to this section.');
        }
    }

    public function showprojectsAction(){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $projects=$em->getRepository('IPSSymfonyProjectBundle:Project')->findAll();
        $projects_auth=[];
        foreach ($projects as $project){
            if ($this->checkproject($project,$this->getUser())){
                array_push($projects_auth,$project);
            }
        }
        $projects=$projects_auth;
        $j=0;
        foreach ($projects as $project){
            $sections=$em->getRepository('IPSSymfonyProjectBundle:Section')->findBy(array('PROJECT'=>$project));
            $i=0;$cumul=0;
            foreach ($sections as $section){
                $cumul=$cumul+$section->getSTATUT();
                $i=$i+1;
            }
            if ($i==0){
                $i=1;
            }
            $projects[$j]->progress=$cumul/$i;
            $j=$j+1;
        }
        // print_r($projects[0]);
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::projects.html.twig',
            array('projects'  => $projects)
        );  
    }

    public function editprojectsAction(){
        $this->checkLogin();
        $rep_proj=$this->getDoctrine()->getManager()->getRepository('IPSSymfonyProjectBundle:Project');
        $projects=$rep_proj->findAll();
        $projects_auth=[];
        foreach ($projects as $project){
            if ($this->checkproject($project,$this->getUser())){
                array_push($projects_auth,$project);
            }
        }
        $projects=$projects_auth;
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::edit_projects.html.twig',
            array('projects'  => $projects)
        );  
    }
    public function editprojectAction($id){
        $this->checkLogin();
        $rep_proj=$this->getDoctrine()->getManager()->getRepository('IPSSymfonyProjectBundle:Project');
        $project=$rep_proj->find($id);
        $this->checkprojectaccess($project,$this->getUser());
        $fm=$e=$g=$m=$p=$c=$vhigh=$high=$medium=$low=$vlow="";
        //pour la preselection de l'importance
        if ($project->getIMPORTANCE()=="very-high"){
            $vhigh="selected";
        }
        if ($project->getIMPORTANCE()=="high"){
            $high="selected";
        }
        if ($project->getIMPORTANCE()=="medium"){
            $medium="selected";
        }
        if ($project->getIMPORTANCE()=="very-low"){
            $vlow="selected";
        }
        if ($project->getIMPORTANCE()=="low"){
            $low="selected";
        }
        //pour la selection du domaine
        if ($project->getDOMAIN()=="fluid-mechanics"){
            $fm="selected";
        }
        if ($project->getDOMAIN()=="geology"){
            $g="selected";
        }
        if ($project->getDOMAIN()=="mathematics"){
            $m="selected";
        }
        if ($project->getDOMAIN()=="physics"){
            $p="selected";
        }
        if ($project->getDOMAIN()=="chemistry"){
            $c="selected";
        }
        if ($project->getDOMAIN()=="environment"){
            $e="selected";
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::edit_project.html.twig',
            array('project'  => $project,
                  'DEADLINE'=>$project->getDEADLINE()->format("m-d-Y"),
                  'fm'=>$fm,
                  'e'=>$e,
                  'm'=>$m,
                  'g'=>$g,
                  'p'=>$p,
                  'c'=>$c,
                  'high'=>$high,
                  'vhigh'=>$vhigh,
                  'medium'=>$medium,
                  'low'=>$low,
                  'vlow'=>$vlow,)
        ); 
    }

    public function updateprojectAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($id);
        $this->checkprojectaccess($project,$this->getUser());
        $project->update($_POST["name"],$_POST["importance"],$_POST["deadline"],$_POST["domain"],$_POST["comment"]);
        $em->flush();
        return $this->editprojectsAction();
    }

    public function deleteprojectAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($id);
        $this->checkprojectaccess($project,$this->getUser());
        $em->remove($project);
        $em->flush();
        return $this->editprojectsAction();
    }

    public function addsectionAction($proj,$crea_msg=""){
        $this->checkLogin();
        $msg="";
        if ($crea_msg=="OK"){
            $msg='A new section has been added successfully!';
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::new_section.html.twig',
            array('proj'  => $proj,
                  'msg' => $msg)
        ); 
    }
    public function checkaccess($role){
        $this->denyAccessUnlessGranted($role);
    }

    public function addmemberAction($proj,Request $request){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $msg="";
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($proj);
        $this->checkprojectaccess($project,$this->getUser());
        $this->checkaccess('ROLE_MANAGER');
        $userManager = $this->get('fos_user.user_manager');
        if ($request->isMethod('POST')){
            $newmember=$userManager->findUserByUsername($_POST['newmember']);
            $project->addUser($newmember);
            $em->persist($project);
            $em->flush();
            $msg="The new member has been added successfully!";
        }
        $users=$userManager->findUsers();
        $members=$project->getUSERS();
        
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::add_members.html.twig',
            array('members'  => $members,
                  'users' => $users,
                  'project' => $project,
                  'msg' => $msg)
        ); 
    }
    public function removememberAction($proj,$memb){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $msg="";
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($proj);
        $this->checkprojectaccess($project,$this->getUser());
        $this->checkaccess('ROLE_MANAGER');
        $userManager = $this->get('fos_user.user_manager');
        $trmember=$userManager->findUserByUsername($memb);
        $project->removeUser($trmember);
        $em->persist($project);
        $em->flush();
        return $this->addmemberAction($proj,new Request());
    }

    public function savesectionAction(){
        $this->checkLogin();
        $section=new Section();
        $em=$this->getDoctrine()->getManager();
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($_POST['proj']);
        $this->checkprojectaccess($project,$this->getUser());
        $section->init($_POST["name"],$_POST["importance"],$_POST["comment"],$project);
        $em->persist($section);
        $em->flush();
        return $this->showprojectAction($project->getId());
        //return $this->addsectionAction($project->getId(),"OK");
    }

    public function showprojectAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $project=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($id);
        $this->checkprojectaccess($project,$this->getUser());
        $sections=$em->getRepository('IPSSymfonyProjectBundle:Section')->findBy(array('PROJECT'=>$project));
        $j=0;
        foreach ($sections as $section){
            $tasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('SECTION'=>$section));
            $sections[$j]->tasks=$tasks;
            $j=$j+1;
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::project.html.twig',
            array('project'  => $project,
                  'sections' => $sections)
        ); 
    }

    public function addtaskAction($sect,$crea_msg=""){
        $this->checkLogin();
        $userManager = $this->get('fos_user.user_manager');
        $users=$userManager->findUsers();
        $msg="";
        if ($crea_msg=="OK"){
            $msg='A new task has been added successfully!';
        }

        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::new_task.html.twig',
            array('sect'  => $sect,
                  'msg' => $msg,
                  'users' => $users)
        ); 
    }

    public function savetaskAction(){
        $this->checkLogin();
        $task=new Task();
        $em=$this->getDoctrine()->getManager();
        $section=$em->getRepository('IPSSymfonyProjectBundle:Section')->find($_POST['sect']);
        $this->checkprojectaccess($section->getPROJECT(),$this->getUser());
        $task->init($_POST["name"],$_POST["importance"],$_POST["incharge"],$_POST["comment"],$section,$this->getUser()->getUsername());
        $em->persist($task);
        $em->flush();
        $this->updatestatut($section);
        return $this->showsectionAction($section->getId());
        //return $this->addtaskAction($section->getId(),"OK");
    }

    public function updatestatut(Section $section){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $tasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('SECTION'=>$section));
        $i=0;$cumul=0;
        foreach ($tasks as $task){
            $cumul=$cumul+$task->getSTATUT();
            $i=$i+1;
        }
        if ($i==0){
            $i=1;
        }
        $section->setSTATUT(floor($cumul/$i));
        $em->flush();
    }

    public function addreferenceAction($task_id,Request $request){
        $this->checkLogin();
        $msg="";
        $reference=new Reference();
        $form_ref = $this->get('form.factory')->create(ReferenceType::class, $reference);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            // Ajoutez cette ligne :
            // c'est elle qui déplace l'image là où on veut les stocker
            $reference->setTYPE("link");
            $em = $this->getDoctrine()->getManager();
            $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($task_id);
            // $task=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($task->getSECTION()->get);
            $reference->upload($task->getSECTION()->getPROJECT()->getNAME(),$task->getSECTION()->getNAME(),$task->getNAME());
            // print_r($reference);
            // Le reste de la méthode reste inchangé
            // print_r($task);
            $reference->setTASK($task);
            $em->persist($reference);
            $em->flush();
            $msg='The new reference has been added successfully!';
            return $this->showtaskAction($task_id,new Request());
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::new_reference.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'task_parent' => $task_id,
                  'msg' => $msg)
        ); 
    }

    public function addworkAction($task_id,Request $request){
        $this->checkLogin();
        $msg="";
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            // Ajoutez cette ligne :
            // c'est elle qui déplace l'image là où on veut les stocker
            $work->setTYPE("html");
            $em = $this->getDoctrine()->getManager();
            $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($task_id);
            // $task=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($task->getSECTION()->get);
            $work->upload($task->getSECTION()->getPROJECT()->getNAME(),$task->getSECTION()->getNAME(),$task->getNAME());
            // print_r($reference);
            // Le reste de la méthode reste inchangé
            // print_r($task);
            $work->setTASK($task);
            $work->setSEEN(0);
            $task->setSTATUT(50);
            if ($work->getCONTENT()!=null){
                // $work->setCONTENT(htmlspecialchars($work->getCONTENT()));
            }
            
            $work->setADDDATE(new \DateTime());
            $em->persist($work);
            $em->persist($task);
            $em->flush();
            $msg='The new reference has been added successfully!';
            return $this->showtaskAction($task_id,new Request());
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::new_work.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'task_parent' => $task_id,
                  'msg' => $msg)
        ); 
    }

    public function showtaskAction($id,Request $request){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($id);
        if ($task->getINCHARGE()!=$this->getUser()->getUsername()){
            $this->checkprojectaccess($task->getSECTION()->getPROJECT(),$this->getUser());
        }
        $references=$em->getRepository('IPSSymfonyProjectBundle:Reference')->findBy(array('TASK'=>$task));
        $works=$em->getRepository('IPSSymfonyProjectBundle:Work')->findBy(array('TASK'=>$task));
        $comments=$em->getRepository('IPSSymfonyProjectBundle:Comment')->findBy(array('task'=>$task));
        $add="";$end="";
        foreach ($works as $work){
            if ($this->getUser()->getUsername()==$task->getADDER()){
                $work->setSEEN($work->getSEEN()+1);
                $em->persist($work);
                $em->flush();
            }
        }
        foreach ($comments as $comment){
            if ($this->getUser()->getUsername()==$task->getADDER()){
                $comment->setSEEN($comment->getSEEN()+1);
                $em->persist($comment);
                $em->flush();
            }
        }

        if ($task->getADDDATE()!=null){
            $add=$task->getADDDATE()->format("m-d-Y");
        }
        if ($task->getENDDATE()!=null) {
            $end=$task->getENDDATE()->format("m-d-Y");
        }
        //to add posts
        $comment=new Comment();
        $form_ref = $this->get('form.factory')->create(CommentType::class, $comment);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            // Ajoutez cette ligne :
            // c'est elle qui déplace l'image là où on veut les stocker
            $comment->setSubject("task");
            $em = $this->getDoctrine()->getManager();
            $comment->setProject($task->getSECTION()->getPROJECT());
            $comment->setSection($task->getSECTION());
            $comment->setSender($this->getUser());
            // Le reste de la méthode reste inchangé
            // print_r($task);
            $comment->setTask($task);
            $task->setSTATUT(50);
            $comment->setSeen(0);
            $comment->setADDDATE(new \DateTime());
            $em->persist($comment);
            $em->persist($task);
            $em->flush();
            return $this->showtaskAction($id,new Request());
        }
        //
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::task.html.twig',
            array('task'  => $task,
                  'references' => $references,
                  'works' => $works,
                  'section' => $task->getSECTION(),
                  'project' => $task->getSECTION()->getPROJECT(),
                  'ADDDATE'=>$add,
                  'ENDDATE'=>$end,
                  'form_ref'  => $form_ref->createView(),
                  'comments' => $comments)
        ); 
    }

    public function showsectionAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $section=$em->getRepository('IPSSymfonyProjectBundle:Section')->find($id);
        $this->checkprojectaccess($section->getPROJECT(),$this->getUser());
        $tasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('SECTION'=>$section));
        $add="";$end="";$i=0;
        foreach ($tasks as $task){
            if ($task->getADDDATE()!=null){
                $add=$task->getADDDATE()->format("m-d-Y");
            }
            if ($task->getENDDATE()!=null) {
                $end=$task->getENDDATE()->format("m-d-Y");
            }
            $tasks[$i]->end=$end;
            $tasks[$i]->add=$add;
            $i=$i+1;
        }
        $add="";$end="";
        if ($section->getADDDATE()!=null){
            $add=$section->getADDDATE()->format("m-d-Y");
        }
        if ($section->getENDDATE()!=null) {
            $end=$section->getENDDATE()->format("m-d-Y");
        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::section.html.twig',
            array('tasks'  => $tasks,
                  'section' => $section,
                  'project' => $section->getPROJECT(),
                  'add' => $add,
                  'end' => $end)
        ); 
    }

    public function endtaskAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($id);
        $task->setSTATUT(100);
        $task->setENDDATE(new \DateTime());
        $em->flush();
        $this->updatestatut($task->getSECTION());
        return $this->showtaskAction($id,new Request());
    }

    public function showworkAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $work=$em->getRepository('IPSSymfonyProjectBundle:Work')->find($id);
        // $work->setCONTENT(htmlspecialchars($work->getCONTENT()));
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::work.html.twig',
            array('work'  => $work)
        ); 
    }

    public function mytasksAction(){
        $this->checkLogin();
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $mytasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('iNCHARGE'=>$user->getUsername()));
        return $this->render('IPSSymfonyProjectBundle::my_tasks.html.twig',array(
            'mytasks'=>$mytasks
        ));
    }

    public function staffAction(){
        $this->checkLogin();
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $staff=$em->getRepository('ResearchLabUserBundle:User')->findAll();
        return $this->render('IPSSymfonyProjectBundle::staff.html.twig',array(
            'staff'=>$staff
        ));
    }

    public function deletestaffAction($username) {
        $this->checkLogin();
        $userManager = $this->get('fos_user.user_manager');
        /* @var $userManager UserManager */
      
        $user = $userManager->findUserByUsername($username);
        if(\is_null($user)) {
          // user not found, generate $notFoundResponse
          return $notFoundResponse;
        }
      
        \assert(!\is_null($user));
        $userManager->deleteUser($user);
      
        // okay, generate $okayResponse
        return $this->staffAction();
    }
    public function showreferencesprojectAction($id){
        $this->checkLogin();
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query=$em->createQuery('SELECT ref FROM IPS\SymfonyProjectBundle\Entity\Reference ref, IPS\SymfonyProjectBundle\Entity\Task t, IPS\SymfonyProjectBundle\Entity\Section s, IPS\SymfonyProjectBundle\Entity\Project p WHERE ref.TASK=t.id AND t.SECTION=s.id AND s.PROJECT='.$id);
        $references=$query->getResult();
        return $this->render('IPSSymfonyProjectBundle::ref.project.html.twig',array(
            'references'=>$references
        ));
    }
    public function showreferencessectionAction($id){
        $this->checkLogin();
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query=$em->createQuery('SELECT ref FROM IPS\SymfonyProjectBundle\Entity\Reference ref, IPS\SymfonyProjectBundle\Entity\Task t, IPS\SymfonyProjectBundle\Entity\Section s, IPS\SymfonyProjectBundle\Entity\Project p WHERE ref.TASK=t.id AND t.SECTION='.$id);
        $references=$query->getResult();
        return $this->render('IPSSymfonyProjectBundle::ref.section.html.twig',array(
            'references'=>$references
        ));
    }
    public function editstaffAction($username) {
        $this->checkLogin();
        $userManager = $this->get('fos_user.user_manager');
        /* @var $userManager UserManager */
      
        $user = $userManager->findUserByUsername($username);
        if(\is_null($user)) {
          // user not found, generate $notFoundResponse
          return $notFoundResponse;
        }
      
        \assert(!\is_null($user));
        $user->setRoles(array($_POST["new_role"]));
        $userManager->updateUser($user);     
        // okay, generate $okayResponse
        return $this->staffAction();
    }
    public function showtoolsAction($task_id=8,Request $request){
        $this->checkLogin();
        $msg="";
        $work=new Work();
        $em = $this->getDoctrine()->getManager();
        $tools_proj=$em->getRepository('IPSSymfonyProjectBundle:Project')->find(3);
        $this->checkprojectaccess($tools_proj,$this->getUser());
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            // Ajoutez cette ligne :
            // c'est elle qui déplace l'image là où on veut les stocker
            $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($task_id);
            // $task=$em->getRepository('IPSSymfonyProjectBundle:Project')->find($task->getSECTION()->get);
            $work->upload("Tools",$_POST["domain"],$_POST["name"]);
            // print_r($reference);
            // Le reste de la méthode reste inchangé
            // print_r($task);
            $work->setTASK($task);
            $work->setTYPE("tool");
            $work->setSEEN(0);
            if ($work->getCONTENT()!=null){
                // $work->setCONTENT(htmlspecialchars($work->getCONTENT()));
            }
            
            $work->setADDDATE(new \DateTime());
            $em->persist($work);
            $em->flush();
            $msg='The new tool has been added successfully!';
            // ...
        }
        $tools=$em->getRepository('IPSSymfonyProjectBundle:Work')->findBy(array('tYPE' => 'tool'));
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::tools.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'task_parent' => $task_id,
                  'msg' => $msg,
                  'tools' => $tools)
        ); 
    }
    public function usetoolsAction(){
        $this->checkLogin();
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::tools_list.html.twig',
            array());
    }
    public function useparticletracingtoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/particle_tracing_segments_example.rar';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs("Usable_Tools","Particle_tracing_launcher",$user->getUsername());
            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name)){
                $new_name="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name)){
                $new_name="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::particle_tracing_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }
            $pwd=getcwd().'\uploads\Usable_Tools\Particle_tracing_launcher';
            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try output=MF('.$_POST['inlet-head'].','.$_POST['outlet-head'].',\''.$_POST['flow-direction'].'\','.$_POST['num-particles'].','.$_POST['time-step'].','.$_POST['simulation-time'].',\''.$user->getUsername().'\',\''.$new_name.'\');exit; catch return;end" -logfile "'.$user->getUsername().'\log.txt"';

            //$cmd=$cmd.' '.'"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results
                // Get real path for our folder
                //$cmd='"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
       
                //$last_line = exec($cmd, $output, $retval);
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::particle_tracing_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                           'file_path' => false,
                       'file_seg' => $file_seg)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your segments file and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::particle_tracing_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function useparticletracingtoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\Particle_tracing_launcher';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/Particle_tracing_launcher/'.$user->getUsername().'/results_sim_part_trac.zip';
                }
                //$inhtml=$inhtml.'</br>'.$line;
            }

            fclose($fh);   
        }
            

        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::particle_tracing_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path)
                    );

    }
    public function deletetaskAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($id);
        $sect=$task->getSECTION();

        $this->deletetaskcascade($task->getId());

        $this->updatestatut($sect);
        return $this->showsectionAction($sect->getId());
    }
    public function deletetaskcascade($id){
        $em=$this->getDoctrine()->getManager();
        $task=$em->getRepository('IPSSymfonyProjectBundle:Task')->find($id);
        $works=$em->getRepository('IPSSymfonyProjectBundle:Work')->findBy(array('TASK'=>$task));
        foreach ($works as $work){
            $em->remove($work);
        }
        $references=$em->getRepository('IPSSymfonyProjectBundle:Reference')->findBy(array('TASK'=>$task));
        foreach ($references as $reference){
            $em->remove($reference);
        }
        $em->remove($task);
        $em->flush();
    }

    public function deletesectioncascade($id){
        $em=$this->getDoctrine()->getManager();
        $section=$em->getRepository('IPSSymfonyProjectBundle:Section')->find($id);
        $tasks=$em->getRepository('IPSSymfonyProjectBundle:Task')->findBy(array('SECTION'=>$section));
        foreach ($tasks as $task){
            $this->deletetaskcascade($task->getId());
        }
        $em->remove($section);
        $em->flush();
    }
    public function deletesectionAction($id){
        $this->checkLogin();
        $em=$this->getDoctrine()->getManager();
        $section=$em->getRepository('IPSSymfonyProjectBundle:Section')->find($id);
        $proj=$section->getPROJECT();
        $this->deletesectioncascade($section->getId());

       
        return $this->showprojectAction($proj->getId());
    }
    public function usecarbonatedissolutiontoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/carbonate_dissolution_segments_example.rar';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs("Usable_Tools","Carbonate_dissolution_launcher\Dissolution_2D",$user->getUsername());
            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name)){
                $new_name="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name)){
                $new_name="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::carbonate_dissolution_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }
            $res_img_path='/uploads/Usable_Tools/Carbonate_dissolution_launcher/Dissolution_2D/'.$user->getUsername().'/Results/apert_evol.png';
            $pwd=getcwd().'\uploads\Usable_Tools\Carbonate_dissolution_launcher\Dissolution_2D';
            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try output=MainGrowth('.$_POST['inlet-head'].','.$_POST['outlet-head'].',\''.$_POST['flow-direction'].'\','.$_POST['num-iterations'].','.$_POST['time-step'].','.$_POST['kinetics'].',\''.$user->getUsername().'\',\''.$new_name.'\');exit; catch return;end" -logfile "'.$pwd.'\\'.$user->getUsername().'\log.txt"';

            //$cmd=$cmd.' '.'"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results
                // Get real path for our folder
                //$cmd='"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
       
                //$last_line = exec($cmd, $output, $retval);
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::carbonate_dissolution_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                          'res_img_path' => $res_img_path,
                           'file_path' => false,
                       'file_seg' => $file_seg)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your segments file and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::carbonate_dissolution_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function usecarbonatedissolutiontoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\Carbonate_dissolution_launcher\Dissolution_2D';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/Carbonate_dissolution_launcher/Dissolution_2D/'.$user->getUsername().'/results_sim_car_diss.zip';
                }
                //$inhtml=$inhtml.'</br>'.$line;
            }

            fclose($fh);   
        }
            
        $res_img_path='/uploads/Usable_Tools/Carbonate_dissolution_launcher/Dissolution_2D/'.$user->getUsername().'/Results/apert_evol.png';
        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::carbonate_dissolution_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path,
                            'res_img_path' => $res_img_path)
                    );

    }
    public function useflowmeterinterptoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/flowmeter_interp_data_example.txt';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs("Usable_Tools","Flowmeter_interp_launcher",$user->getUsername());
            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name)){
                $new_name="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name)){
                $new_name="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::flowmeter_interp_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }
            $pwd=getcwd().'\uploads\Usable_Tools\Flowmeter_interp_launcher';
            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try output=FlowInterp('.$_POST['Qt'].','.$_POST['wellbore'].','.$_POST['Keff'].',\''.$_POST['title'].'\','.$_POST['zonation'].',\''.$user->getUsername().'\',\''.$new_name.'\');exit; catch return;end" -logfile "'.$pwd.'\\'.$user->getUsername().'\log.txt"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results       
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::flowmeter_interp_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                           'file_path' => false,
                       'file_seg' => $file_seg)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your segments file and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::flowmeter_interp_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function useflowmeterinterptoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\Flowmeter_interp_launcher';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/Flowmeter_interp_launcher/'.$user->getUsername().'/results_interp_flowmeter.zip';
                }
            }

            fclose($fh);   
        }

        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::flowmeter_interp_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path)
                    );

    }
    public function usepressureplottoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/p_vs_t.txt';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs("Usable_Tools","Pressure_plot_launcher",$user->getUsername());
            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name)){
                $new_name="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name)){
                $new_name="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::pressure_plot_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }
            $pwd=getcwd().'\uploads\Usable_Tools\Pressure_plot_launcher';
            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try MF(\''.$user->getUsername().'\',\''.$new_name.'\');exit; catch return;end" -logfile "'.$pwd.'\\'.$user->getUsername().'\log.txt"';

            //$cmd=$cmd.' '.'"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results
                // Get real path for our folder
                //$cmd='"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
       
                //$last_line = exec($cmd, $output, $retval);
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::pressure_plot_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                           'file_path' => false,
                       'file_seg' => $file_seg)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your segments file and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::pressure_plot_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function usepressureplottoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\Pressure_plot_launcher';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/Pressure_plot_launcher/'.$user->getUsername().'/results_plot_drawdown.zip';
                }
                //$inhtml=$inhtml.'</br>'.$line;
            }

            fclose($fh);   
        }
            

        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::pressure_plot_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path)
                    );

    }
    public function showhighchartAction(Request $resuest){
        $this->checkLogin();
        $pwd=getcwd().'\uploads\Usable_Tools\Highcharts_data';
        $row = 1;
        if (file_exists($pwd.'\\'.$this->getUser()->getUsername().'\test.csv')){
            if (($handle = fopen($pwd.'\\'.$this->getUser()->getUsername().'\test.csv', 'r')) !== FALSE) {
                $csv=array();
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $num = count($data);
                    //echo "<p> $num champs à la ligne $row: <br /></p>\n";
                    $row++;
                    for ($c=0; $c < $num; $c++) {
                        //$data[$c] . "<br />\n";
                        if ($c==0){
                            $title=$data[$c][0];
                        }
                        elseif ($c==1) {
                            # code...
                            $xtitle=$data[$c][0];
                            $ytitle=$data[$c][1];
                        }
                        array_push($csv,array_map('doubleval',explode(',',$data[$c])));
                    }
                }
                fclose($handle);
            }
        }
        // Chart
        print_r($csv[1]);
        $series = array(
            array("name" => "Data Serie 1",    "data" => array_slice($csv, 2))
        );

        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->title->text('drawdown');
        $ob->xAxis->title(array('text'  => ''));
        $ob->yAxis->title(array('text'  => ''));
        $ob->series($series);

        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::highchartsphp.html.twig',
                    array('chart'  => $ob)
                );
    }

    public function useswmmtoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/swmm_example.rar';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs("Usable_Tools","SWMM",$user->getUsername());
            $work->upload_inputs2("Usable_Tools","SWMM",$user->getUsername());

            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name)){
                $new_name="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name)){
                $new_name="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::swmm_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }

            $name2 = $work->getFile2()->getClientOriginalName();
            if (preg_match("/(.+).mat$/", $name2)){
                $new_name2="mat";
            }
            elseif (preg_match("/(.+)txt$/", $name2)){
                $new_name2="txt";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::swmm_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }


            $res_img_path='/uploads/Usable_Tools/SWMM/'.$user->getUsername().'/Results/apert_evol.png';
            $pwd=getcwd().'\uploads\Usable_Tools\SWMM';
            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try output=SWMM_main('.$_POST['YTFfit'].','.$_POST['YDYNIA'].','.$_POST['inflow_velocity'].','.$_POST['influx'].','.$_POST['outflux'].','.$_POST['TimeOfInjection'].','.$_POST['EndSimulation'].',\''.$_POST['time-step'].'\','.$_POST['Rainfall'].',\''.$user->getUsername().'\',\''.$new_name.'\',\''.$new_name2.'\');exit; catch return;end" -logfile "'.$pwd.'\\'.$user->getUsername().'\log.txt"';

            //$cmd=$cmd.' '.'"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results
                // Get real path for our folder
                //$cmd='"C:\Program Files (x86)\WinRAR\Rar" a "'.$pwd.'\\'.$user->getUsername().'\results_sim_part_trac.zip" "'.$pwd.'\\'.$user->getUsername().'\Results"';
       
                //$last_line = exec($cmd, $output, $retval);
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::swmm_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                          'res_img_path' => $res_img_path,
                           'file_path' => false,
                       'file_seg' => $file_seg)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your segments file and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::swmm_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function useswmmtoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\SWMM';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/SWMM/'.$user->getUsername().'/results_sim_swmm.zip';
                }
                //$inhtml=$inhtml.'</br>'.$line;
            }

            fclose($fh);   
        }
            
        $res_img_path='/uploads/Usable_Tools/SWMM/'.$user->getUsername().'/Results/apert_evol.png';
        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::swmm_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path,
                            'res_img_path' => $res_img_path)
                    );

    }



    public function usehttoolAction(Request $request){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $work=new Work();
        $file_seg='/bundles/IPS/files/ht_data_example.rar';
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        if ($request->isMethod('POST') && $form_ref->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $work->upload_inputs3("Usable_Tools","HT_launcher",$user->getUsername());
            $name = $work->getFile()->getClientOriginalName();
            if (preg_match("/(.+).zip$/", $name)){
                $new_name="zip";
            }
            elseif (preg_match("/(.+)rar$/", $name)){
                $new_name="rar";
            }else{
                $msg_err="the format is not correct!";
                return $this->get('templating')->renderResponse(
                'IPSSymfonyProjectBundle::ht_launcher.html.twig',
                array('form_ref'  => $form_ref->createView(),
                      'msg' => $msg,
                      'msg_err' => $msg_err,
                      'file_path' => false,
                      'inhtml' => false,
                       'file_seg' => $file_seg)
                );
            }
            $pwd=getcwd().'\uploads\Usable_Tools\HT_launcher';
            $res_img_path='/uploads/Usable_Tools/HT_launcher/'.$user->getUsername().'/Results/invlogT.png';
            //$process_cms=proc_open('"C:\Program Files\COMSOL\COMSOL55\Multiphysics\bin\win64\comsolmphserver" -logfile "'.$pwd.'\\'.$user->getUsername().'\logcmssrvr.txt"');

            $cmd = '"'.$this->container->getParameter('matlab_folder').'\matlab" -automation -sd ' . $pwd . ' -r "try MF_HT('.$_POST['Tinitial'].','.$_POST['SigmaCovariance'].','.$_POST['Lx'].','.$_POST['Ly'].','.$_POST['nx'].','.$_POST['ny'].','.$_POST['Tm'].',\''.$user->getUsername().'\',\''.$this->container->getParameter('comsol_folder').'\',\''.$this->container->getParameter('winrar_folder').'\',\''.$this->container->getParameter('comsol_username').'\',\''.$this->container->getParameter('comsol_password').'\',\''.$this->container->getParameter('comsol_host').'\');exit; catch return;end" -logfile "'.$pwd.'\\'.$user->getUsername().'\log.txt"';
            
            $last_line = exec($cmd, $output, $retval);
            $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
            $inhtml='In progress...';
            if (file_exists($logfile)){
                $fh = fopen($logfile,'r');
                
                while ($line = fgets($fh)) {
                  // <... Do your work with the line ...>
                    $inhtml=$inhtml.$line;
                }
                fclose($fh);   
            }
            //print_r($cmd);
            if ($retval == 0){
                // zip the results       
                $msg='The simulation is done successfully!';
                return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::ht_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => false,
                            'file_seg' => $file_seg,
                            'res_img_path' => $res_img_path)
                    );
            } else {
                // When command failed
                $msg_err='An error occured during the simulation, please revise your input files and make sure to give a reasonable parameters.';
            }

            

        }
        return $this->get('templating')->renderResponse(
            'IPSSymfonyProjectBundle::ht_launcher.html.twig',
            array('form_ref'  => $form_ref->createView(),
                  'msg' => $msg,
                  'msg_err' => $msg_err,
                  'file_path' => false,
                  'inhtml' => false,
                       'file_seg' => $file_seg)
        );
    }
    public function usehttoolrefreshAction(){
        $this->checkLogin();
        $msg="";
        $msg_err="";
        $user = $this->getUser();
        $work=new Work();
        $form_ref = $this->get('form.factory')->create(WorkType::class, $work);
        $file_path="";
        $pwd=getcwd().'\uploads\Usable_Tools\HT_launcher';
        $logfile=$pwd.'\\'.$user->getUsername().'\\log.txt';
        $inhtml='In progress...';
        if (file_exists($logfile)){
            $fh = fopen($logfile,'r');
            
            while ($line = fgets($fh)) {
              // <... Do your work with the line ...>
                $inhtml=$line;
                if ('Done-signal.'== $line){
                    
                    $inhtml='Finished!';
                    $file_path='/uploads/Usable_Tools/HT_launcher/'.$user->getUsername().'/results_ht.zip';
                }
            }

            fclose($fh);   
        }
        $res_img_path='/uploads/Usable_Tools/HT_launcher/'.$user->getUsername().'/Results/invlogT.png';
        return $this->get('templating')->renderResponse(
                    'IPSSymfonyProjectBundle::ht_launcher.html.twig',
                    array('form_ref'  => $form_ref->createView(),
                          'msg' => $msg,
                          'msg_err' => $msg_err,
                          'inhtml' => $inhtml,
                            'file_path' => $file_path,
                            'res_img_path' => $res_img_path)
                    );

    }
}
    
