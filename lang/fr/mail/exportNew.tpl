<p>Un nouvel export <b>{$mail.title}</b> vient d'être créé sur <a href='http://www.radarvo.com'>RadarVO</a>.</p>
<p>Les sites suivants ont été ajoutés :</p>
<ul>
    {foreach from=$mail.site item="site"}
        <li>{$site.code|@strtoupper} - {$site.title|@strtolower|capitalize} ({$site.flux})</li>
    {/foreach}
</ul>
{if $mail.type == "put"}
    <p>Les fichiers seront déposés chaque nuit à 5h00 (heure française).</p>
{else}
    <p>Les fichiers seront mis à jour chaque nuit à 5h00 (heure française).</p>
    <p>Vos informations de connexion sont :</p>
    <ul>
        <li><b>Serveur FTP :</b> ftp.radarvo.com</li>
        <li><b>Nom d'utilisateur</b> : {$mail.username}@radarvo.com</li>
        <li><b>Mot de passe</b> : {$mail.password}</li>
    </ul>
{/if}
<p>Le premier export sera mis à jour dès cette nuit.</p>

<p>Cordialement,</p>
<p>L'équipe RadarVO.</p>