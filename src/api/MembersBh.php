<?php
namespace breadhead\mailchimp\api;

class MembersBh extends EcommerceEntity
{
    public function updateMember(string $listId, string $email, array $data)
    {
        return $this->client->execute("PATCH", "lists/{$listId}/members/{$this->getMemberHash($email)}", $data);
    }

    public function deleteMemberPermanently(string $listId, string $email)
    {
        return $this->client->execute('DELETE', "lists/{$listId}/members/{$this->getMemberHash($email)}");
    }

    protected function getMemberHash(string $email): string
    {
        return md5(strtolower($email));
    }
}
