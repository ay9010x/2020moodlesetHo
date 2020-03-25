<?php




class CAS_Languages_French implements CAS_Languages_LanguageInterface
{
    
    public function getUsingServer()
    {
        return 'utilisant le serveur';
    }

    
    public function getAuthenticationWanted()
    {
        return 'Authentication CAS nécessaire&nbsp;!';
    }

    
    public function getLogout()
    {
        return 'Déconnexion demandée&nbsp;!';
    }

    
    public function getShouldHaveBeenRedirected()
    {
        return 'Vous auriez du etre redirigé(e) vers le serveur CAS. Cliquez <a href="%s">ici</a> pour continuer.';
    }

    
    public function getAuthenticationFailed()
    {
        return 'Authentification CAS infructueuse&nbsp;!';
    }

    
    public function getYouWereNotAuthenticated()
    {
        return '<p>Vous n\'avez pas été authentifié(e).</p><p>Vous pouvez soumettre votre requete à nouveau en cliquant <a href="%s">ici</a>.</p><p>Si le problème persiste, vous pouvez contacter <a href="mailto:%s">l\'administrateur de ce site</a>.</p>';
    }

    
    public function getServiceUnavailable()
    {
        return 'Le service `<b>%s</b>\' est indisponible (<b>%s</b>)';
    }
}

?>