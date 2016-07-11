page_title: Mumble : qu'est-ce que c'est, comment l'utiliser ?
title: Mumble
subtitle: Qu'est-ce que c'est, comment l'utiliser ?
---

*Longtemps, ~~je me suis couché de bonne heure~~ j'ai cherché à utiliser Mumble en lieu et place de Skype pour diverses occasions, que ce soit pour le jeu, la discussion audio toute simple... Mais la résistance était rude. L'argument principal ? « Je ne comprends pas très bien comment ça marche ». Pour enfin pouvoir l'utiliser, j'ai donc décidé de rédiger un tutoriel à l'usage des personnes ne connaissant pas du tout Mumble et son mode de fonctionnement, en m'arrangeant pour qu'il soit le plus simple, rapide à lire et précis possible.*

*Si vous connaissez déjà Mumble, n'espérez pas trouver ici des informations extraordinaires. Je me contente actuellement des bases du logiciel, soit ce qui est utile au quotidien dans sa manipulation.*

**Notas.**

- Je parlerai ici de la version pour PC de Mumble uniquement, par opposition aux versions mobile.
- Les captures d'écran sont prises sous GNU/Linux (KDE), mais l'interface est identique sous Windows, Mac OS X ou d'autres environnements de GNU/Linux, à très peu de choses près.
- Je considère un serveur Mumble avec les réglages par défaut. Certains serveur exigent une authentification par mot de passe, d'autres permettent la création de plusieurs comptes d'administration... Adaptez-vous dans ces cas.
- Les explications sont données pour le client par défaut de Mumble.
- L'article est entièrement rédigé, mais n'est pas en version définitive. N'hésitez pas à me [donner votre avis](https://amaury.carrade.eu/contact.html) :-) .


### Mumble ? Qu'est-ce donc ?

Mumble est un logiciel de discussion instantanée vocale (et textuelle), autrement qualifié de logiciel de VoIP. Il a initialement été développé pour un usage lors de jeux vidéos, et par conséquent est optimisé pour cet usage. Cependant, cette optimisation profite à tous les autres usages, notament la discussion “classique”.

Parmi les logiciels similaires, on pourra citer le célèbre Skype, bien que ces deux logiciels diffèrent sur leur principe de fonctionnement.


#### Avantages et inconvénients

Parmis ses qualités, on peut noter les quelques points suivants.

- Mumble permet une discussion avec **une latence extrêmement faible**, même sur un réseau très léger.
- De surcroît, Mumble permet des discussions en **haute qualité sonore**, ce qui n'est pas désagréable.
- Les discussions sur Mumble sont **chiffrées de bout en bout par un chiffrement fort**. De quoi tenir à l'écart les curieux qui ne verrons qu'une bouillie d'octets.
- Enfin, Mumble est **libre et open source**.
- C'est ainsi un logiciel pérène dépourvu de portes dérobées (backdoors) utilisées par la NSA et d'autres pour espionner les conversations (espionnage dont Skype n'est pas immunisé, et de loin).

Voilà pour les nombreux avantages de Mumble. Parmis les inconvénients, on pourrai citer le fait que Mumble est un peu plus compliqué à apréhender que d'autres logiciels. Et encore, en général le problème vient plus du changement d'habitudes que d'autres choses. 


#### Principe de fonctionnement

Contrairement à certains logiciels tel Skype, Mumble n'est pas centralisé<sup title="OK, moins centralisé. Il reste une centralisation au niveau des serveurs, mais elle est sans commune mesure avec la centralisation de logiciels tel Skype.">(1)</sup>.
Sur Mumble, vous n'avez pas de liste attribuée de contacts, là n'est pas le but. Vous avez ce que l'on appelle des « serveurs Mumble » ; toutes les personnes connectées à un serveur de discussion donné peuvent parler ensemble.

Autrement dit :

- sur Skype (et équivalents) vous sélectionnez un contact pour parler avec lui, ou vous créez un groupe de discussion pour parler à plusieurs ;
- sur Mumble, vous choisissez (ou créez) un serveur, et vous parlez avec les gens qui y sont.


### Téléchargement et installation

Mumble est un logiciel disponible sur toutes les plateformes courantes. Vous pouvez le télécharger en cliquant sur le bouton ci-dessous. 

<div class="text-center"><a href="http://mumble.info/" class="btn btn-primary">Se rendre sur le site officiel de Mumble et télécharger le logiciel</a>
<br /><small>J'aime les longs titres.</small></div>

Cherchez l'encadré « Get Mumble », et cliquez sur le lien correspondant à votre système d'exploitation (Windows, Mac OS X ou GNU/Linux). Cliquez sur le premier lien (version « Stable »). Si vous êtes sous Mac OS X, prenez soin de prendre la version « OS X », et non la version « OS X (Legacy, Universal) ».
Sous GNU/Linux, vous pouvez aussi généralement installer le paquet `mumble` ([cliquez simplement sur ce lien](apt://mumble) si vous êtes sur Debian, Ubuntu ou leurs dérivées).

Ceci fait, lancez l'installeur téléchargé et suivez les instructions. Si vous êtes sous Windows, vous rencontrerez l'écran suivant : n'y sélectionnez que l'option « Mumble (client) ». L'autre ne sert qu'à créer un serveur Mumble sur votre ordinateur, et ce n'est pas ce que vous voulez.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/1-Install.png" alt="Choix des composants à installer (Windows uniquement)" /><br />
<small>Choix des composants à installer (Windows uniquement)</small></div>


### Configuration de Mumble

Au premier démarrage de Mumble, quelques questions vont vous être posées pour configurer ce dernier dans le cadre d'une utilisation normale. Au programme : réglage du micro, des hauts parleurs, de la manière de parler, et un soupçon de sécurisation.

Il est conseillé de relancer l'assistant audio si vous changez de microphone.


#### Sécurisation

Le premier écran que vous allez voir est présenté ci-dessous. Sauf si vous savez ce que vous faites, sélectionnez l'option « Création automatique d'un certificat », et cliquez sur `Suivant`.

À titre indicatif et pour faire court, ce certificat est votre pièce d'identité sur Mumble, automatiquement présentée au serveur auquel vous vous connectez.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/2-Certificat.png" alt="Configuration du certificat" /><br />
<small>Configuration du certificat</small></div>


#### Configuration de l'audio

Ensuite, l'Assistant Audio doit se lancer. Si ce n'est pas le cas, lancez le manuellement via le menu `Configurer` → `Assistant Audio`. Cet assistant va régler de manière semi automatisée divers paramètres liés à votre microphone et à vos hauts-parleurs, histoire que tout le monde s'entende bien.
Cliquez sur `Suivant`. Le premier écran ne doit normalement pas être modifié. Cliquez à nouveau sur `Suivant`. À partir de ce point, un casque et un microphone sont nécessaires.

Vous arrivez sur l'écran présenté ci-dessous. L'objectif est simple : vous entendez quelqu'un parler en boucle, et vous devez ajuster le curseur jusq'à ce que le son soit parfaitement fluide, sans aucune interruption.
Je vous conseille de commencer à la valeur la plus basse, et si ce n'est pas bon, d'augmenter progressivement jusq'à ce qu'il n'y ai pas de problème. Puis cliquez sur `Suivant`.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/5-AssistantAudio2.png" alt="Réglage du son entendu" /><br />
<small>Réglage du son entendu</small></div>

Ensuite, même programme mais pour le microphone. Je vous invite à simplement lire le texte, inutile de tout recopier. `Suivant`.

Après cela, se configure la manière dont vous allez parler. En effet, il faut savoir que Mumble n'enregistre pas en permanence ce que vous dites, pour économiser de la bande passante. Il va essayer de deviner quand il est nécessaire de transmettre la voix. Et il y a plusieurs manières de lui faire deviner, au choix.

- L'option « **appuyer pour parler** » (ou *push to talk*) permet simplement de lancer la transmission de votre voix en maintenant une touche appuyée. Configurez la touche en question en cliquant sur le champ de texte.
- L'option « **amplitude d'entrée** » est assez intéressante. L'idée est que Mumble va mesurer en permanence à quel point le niveau sonore est élevé, et sitôt dépassé un certain seuil, il lance la transmission. En effet, dés lors que vous parlez, le niveau sonore enregistré par votre micro augmente.
  Il vous faut configurer ce seuil ; je vous laisse lire et exécuter la consigne affichée à l'écran, en déplaçant le curseur au bon endroit.
- L'option « **taux de signal/bruit** » correspond à la même idée, mais dans l'autre sens : c'est le son reçu qui est analysé, au lieu du son du micro.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/7-AssistantAudio4.png" alt="Réglage de l'activation du microphone et de la transmission de la voix" /><br />
<small>Réglage de l'activation du microphone et de la transmission de la voix</small></div>

***Nota** — Il existe un autre mode de parole : le mode de transmission continue. Dans ce mode, votre voix est transmise de manière ininterrompue, impliquant plus de consommation réseau.
Ce mode peut être utile si vous ne voulez pas appuyer en permanence sur une touche pour parler, et si votre voix fluctue trop pour utiliser la détection automatique.*

*Il n'est pas activable via l'assistant audio ; pour l'activer, une fois l'assistant terminé, allez dans Configurer → Paramètres, et dans la première section, devans « Transmission », choisissez « Continu ».*


L'écran suivant concerne la qualité et les notifications reçues. Les paramètres par défaut sont les paramètres recommandés. Vous pouvez régler la qualité à « Bas » si vous avez une *très* mauvaise connexion ; dans les autres cas, je conseillerai de ne toucher à rien et de directement cliquer sur `Suivant`.

Enfin, la dernière étape : le réglage du stéréo (soit de comment Mumble doit positionner la voix des autres joueurs, si possible et nécessaire). Je vous laisse lire le texte et cocher, ou non, la case stipulant que vous utilisez des écouteurs.

La configuration est alors terminée. 


### Utilisation de Mumble

#### Se connecter à un serveur

Nous avons vu que Mumble se base sur le principe d'une connexion à un serveur de discussion. Pour se connecter, il faut passer par cette fenêtre qui normalement s'affiche au démarrage de Mumble (vous pouvez la lancer manuellement via `Serveur` → `Connexion`, ou avec <kbd><kbd>Ctrl</kbd> + <kbd>O</kbd></kbd>).

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/11-Connexion.png" alt="Connexion à un serveur Mumble" /><br />
<small>Connexion à un serveur Mumble</small></div>

Pour vous connecter à un serveur, vous n'avez qu'à double cliquer dessus.

Vous pouvez soit choisir un serveur public de la liste « Internet public », soit vous connecter à un serveur privé. Dans ce dernier cas, il vous faut sélectionner l'option `Ajouter un nouveau...`, entrer un nom de serveur (ce que vous voulez, c'est pour vous) et remplir le petit formulaire qui s'affiche avec les informations qui vous ont été transmises. Le pseudo est libre de choix, tant qu'il est disponible dans ce serveur.

Le serveur est alors enregistré dans vos favoris. Vous n'avez plus qu'à vous y connecter.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/11-Connexion2Form.png" alt="Formulaire de connexion" /><br />
<small>Formulaire de connexion</small></div>

 Une dernière chose ! Si lors de la connexion vous voyez ça (ou similaire), avec l'erreur n<sup>o</sup>2, en vous connectant la première fois : 

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/12-ErreurCertificat.png" alt="« Certificat invalide ! »" /></div>

Alors normalement tout va bien, vous pouvez cliquer sur `Oui`. Dans le cas contraire (si ça marchait avant sur ce serveur et que l'alerte est soudainement apparue, notamment), quelqu'un est peut-être entrain de tenter d'usurper l'identité du serveur Mumble, prenez-le en compte si vous devez parler de sujets sensibles !


#### C'est bon, vous pouvez parler !

L'interface principale de Mumble ressemble à cela.

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/14-Fen%C3%AAtrePrincipale.png" alt="Interface principale de Mumble" /><br />
<small>Interface principale de Mumble</small></div>

Et donc comme le titre l'indique, c'est bon, vous pouvez parler. Il suffit d'appuyer sur la touche ou de parler suffisament fort (selon l'option choisie). Vous pouvez également, même si ce n'est pas le but premier de Mumble, discuter textuellement via la discussion instantanée à gauche.
Cependant, vous ne pouvez actuellement que parler et écrire dans le salon racine.

...

Très bien, quelques précisions s'imposent. 


#### Salons de discussion

 Sur un serveur Mumble donné, tout le monde n'est pas obligé de parler au même endroit. Sinon, ce ne serai pas très pratique ! Comment faire pour s'isoler un peu, discuter en équipes (pour des jeux en ligne notament), etc. ?

C'est pour cela qu'ont été créés les salons de discussion. Ils permettent, justement, d'avoir plusieurs lieux de discussions indépendants (mais je pense que vous avez deviné, vu le nom).
Le salon de discussion par défaut est appelé « Root » (« racine » en anglais).

Maintenant, imaginons que vous désirez créer un nouveau salon de discussion. Et bien c'est très simple : cliquez-droit sur « Root » et choisissez l'option « Ajouter ». Dans le forumulaire qui s'affiche, donnez un nom, éventuellement une description et une position dans la liste des salons du serveur. Puis cliquez sur `OK`. 

***Nota** — Vous ne pouvez faire cela que si vous avez des droits d'accès suffisant sur le serveur.*

<div class="text-center"><img src="https://amaury.carrade.eu/files/articles/mumble/16-Cr%C3%A9erSalon.png" alt="Création d'un salon de discussion" /><br />
<small>Création d'un salon de discussion</small></div>

Voilà, le salon apparaît dans la liste. Double-cliquez dessus pour le rejoindre (si ce n'est déjà fait automatiquement). Désormais, ce que vous dites ne sera entendu (et ce que vous écrivez, vu) que par les personnes qui sont dans le même salon que vous.


#### Enregistrement sur un serveur

*À partir de ce point, aucune information n'est absoluement nécessaire pour utiliser Mumble. Ce qui suit ne fait qu'améliorer la sécurité de vos discussions.*

Imaginons. Vous vous déconnectez d'un serveur Mumble. Qu'est-ce qui empêche quelqu'un d'autre de prendre le même pseudo que vous, et par suite, potentiellement usurper votre identité, ou tout simplement semmer la confusion, volontairement ou non ?

Pour éviter ce genre de désagréments, vous pouvez vous enregistrer sur le serveur. Une fois enregistré :

 - vous serez le seul à pouvoir utiliser votre pseudo, sur le serveur où vous êtes ;
 - les autres sauront que vous êtes authentifiés (une icône s'affiche pour le signaler).

Pour vous enregistrer, cliquez-droit sur votre pseudonyme, et choisissez l'option `S'enregistrer`. (Vous pouvez également faire `Soi` → `Enregistrer`.) C'est tout !
Cependant, veuillez noter : tout enregistrement sur un serveur donné est définitif. Seul l'administrateur du serveur a le pouvoir de vous désenregistrer.

Par ailleurs, un autre micro problème se pose. Comment faire pour se connecter avec le même pseudo via un autre ordinateur (ou une application mobile), du coup ? C'est justement à ça que servent les certificats, qui ont été aperçus au tout début de l'installation.

Le certificat, c'est ce qui vous authentifie, lorsque vous vous enregistrez. C'est un peu comme une carte d'identité, mais pour Mumble. Alors pour pouvoir vous connecter via un autre poste, il suffit de réutiliser le même certificat.
La marche à suivre est très simple.

 - Sur l'“ancien” client, dans le menu `Configurer`, choisissez `Assistant certificat`.
 - Cochez « Exporter le certificat actuel », suivant, `Enregistrer sous...`, et sauvegardez le fichier dans un lieu sûr. N'importe qui ayant ce fichier peut se faire passer pour vous sur Mumble.
 - Sur le “nouveau” client, faites `Configurer` → `Assistant certificat` → `Importer un certificat`, puis sélectionnez le fichier précédent, préalablement transporté (par mail ou clef USB, par exemple). C'est tout.

<small>Une dernière chose : vous pouvez constater que le certificat que vous utilisez, qui est présenté en haut de l'Assistant Certificat, est anonyme. Si vous voulez un certificat associé à votre nom, choisissez l'option « Créer un nouveau certificat », suivez les instructions et enregistrez le certificat produit, de même en lieu sûr. Vous pouvez par ailleurs supprimer tous les autres, et utiliser exclusivement celui-là, de la même manière que précédemment.</small>

<div class="text-center">
<a href="http://creativecommons.org/licenses/by-sa/4.0/deed.fr"><img src="https://amaury.carrade.eu/img/cc-bysa.png" alt="CC BY SA 4.0" /></a><br />
<small>Article écrit le 08 février 2014. Dernière mise jour le 19 février 2014.</small>
</div> 
