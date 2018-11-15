<?php
use Phalcon\Mvc\View;

class VotingsController extends ControllerBase
{
    public function indexAction()
    {
        $votings = Votings::find();

        $this->view->votings = $votings;
    }

    public function editAction($id = 0)
    {
        $answers = [];
        if($id)
        {
            $voting = Votings::findFirst($id);

            if(!$voting)
            {
                return $this->response->redirect('/');
            }

            $answers = $voting->getAnswers();
        }
        else
        {
            $voting = new Votings();
        }

        $this->view->voting = $voting;
        $this->view->answers = $answers;
    }

    public function saveAction($id = 0)
    {
        $this->view->disable();

        if($this->request->isPost())
        {
            $post = $this->request->getPost();

            if($id)
            {
                $voting = Votings::findFirst($id);
                $voting->getAnswers()->delete();
            }
            else
            {
                $voting = new Votings();
            }

            $voting->title = $post['title'];
            $voting->url = $post['url'];
            $voting->end_date = strtotime($post['end_date']);
            $voting->save();

            foreach ($post['answers'] as $answer)
            {
                if(trim($answer))
                {
                    $ans = new VotingsAnswers();
                    $ans->voting_id = $voting->id;
                    $ans->answer = trim($answer);
                    $ans->create();
                }
            }
        }

        return $this->response->redirect('votings/edit/'.$voting->id);
    }

    public function deleteAction($id = 0)
    {
        if($id)
        {
            $voting = Votings::findFirst($id);

            if($voting)
            {
                $voting->getAnswers()->delete();
                $voting->delete();
            }
        }

        return $this->response->redirect('/');
    }

    public function votesAction($voting_id = 0)
    {
        $voting = Votings::findFirst($voting_id);

        if(!$voting)
            return $this->response->redirect('votings');

        $this->view->voting = $voting;
        $this->view->votes = $voting->getVotes();
    }

    public function approveAction($voting_id = 0, $vote_id = 0)
    {
        $voting = Votings::findFirst($voting_id);

        if($voting)
        {
            $vote = Votes::findFirst($vote_id);

            if($vote)
            {
                $vote->confirmed = 1;
                $vote->update();
            }
        }

        return $this->response->redirect('votings/votes/'.$voting->id);
    }

    public function declineAction($voting_id = 0, $vote_id = 0)
    {
        $voting = Votings::findFirst($voting_id);

        if($voting)
        {
            $vote = Votes::findFirst($vote_id);

            if($vote)
            {
                $vote->confirmed = 2;
                $vote->update();
            }
        }

        return $this->response->redirect('votings/votes/'.$voting->id);
    }
}