<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Trick;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrickVoter extends Voter
{
    const TRICK_DELETE = "trick_delete";

    protected function supports(string $attribute, $trick): bool
    {
        return in_array($attribute, [self::TRICK_DELETE])
            && $trick instanceof Trick;
    }

    protected function voteOnAttribute(string $attribute, $trick, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // No author => no permissions
        if($trick->getAuthor() === null) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::TRICK_DELETE:
                return $this->canDelete($trick, $user);
                break;
        }

        return false;
    }

    private function canDelete(Trick $trick, User $user){
        return $user === $trick->getAuthor();
    }
}
