page_title: Débuter en programmation
title: Comment débuter en programmation ?
subtitle: Recommandations et pièges à éviter
---

*Je reçois souvent des demandes sur « comment commencer à programmer ». Histoire de ne pas me répéter de trop nombreuses fois, je vais centraliser sur cette page mes conseils pour répondre à cette question.*

### Ça veut dire quoi, programmer ?

La programmation est une activité bien vaste, et elle l'est de plus en plus de nos jours. *Programmer*, par définition, c'est créer un *programme*. Et des programmes, aujourd'hui, il y en a partout : le navigateur web (Firefox, Chrome...) que vous utilisez en est un, les jeux auxquels vous jouez (le cas échéant) en sont, de même que les traitement de texte, tableurs, ou même le système de base qui fait tourner tout ça (Windows, GNU/Linux, Android, iOS...), les applications mobiles, les télévisions modernes, les montres connectées... Il y a de quoi faire.

Et même là où on s'y attend moins il y en a : un site web est généré par un programme ou un ensemble de programmes (pour le mien le programme de base s'appelle PHP), et envoyé à vous, visiteur, par un autre (appelé *serveur web*).

Et on est loin d'avoir fait le tour.


Ainsi, si vous voulez vous mettre à la programmation, vous aurez l'embarras du choix. Certaines tâches sont plus simples que d'autres mais une bonne partie restent *accessibles* aux débutants (je n'ai pas dit toutes, cela dit — dans d'autres cas il faudra malgré tout un peu plus d'expérience). Vous devrez cela dit systématiquement passer par une phase d'apprentissage des méthodes, mais aussi (et surtout) des concepts et des logiques de raisonnements sous-jacentes. Ce n'est pas compliqué, mais il ne faut pas le négliger ! C'est ce qui fait la différence entre un programmeur qui sait ce qu'il fait, et quelqu'un qui répète bêtement des recettes de cuisine sans vraiment comprendre.


### Comprendre les bases

Il y a une multitude de langages de programmations mais ils ont tous les mêmes concepts de base qu'il est particulièrement utile de connaître si on veut comprendre ce que l'on fait.

Je vous redirige ici vers un cours publié sur le site *Zeste de Savoir* qui traite de cela. Inutile de réécrire ce qui existe déjà :) .

<div class="text-center"><a href="https://zestedesavoir.com/tutoriels/531/les-bases-de-la-programmation/" class="btn btn-primary">Les bases de la programmation<br /><em>sur Zeste de Savoir</em></a></div>

Il est question de qu'est-ce qu'un programme, quelles sont les bases fondatrices partagées par tous, et comment les programmes sont — de manière assez générale — rendus utilisable. Oh et accessoirement, à quoi ça ressemble en pratique, un programme.

**Apparté**  
*Je profite de ce lien pour glisser le mot : débutants en programmation, je recommande chaudement le site [Zeste de Savoir](https://zestedesavoir.com). C'est un site qui propose de nombreux tutoriels clairs pour les débutants (mais pas que...), ainsi qu'un forum d'entre-aide assez dynamique pour poser des questions et recevoir de l'aide. Le site est en plus très chaleureux, alors pourquoi se priver ?*


### Par quoi commencer ?

Il faut d'abord savoir que globalement, tout projet informatique peut être réalisé avec n'importe quel langage de programmation. Cela dit, il y a des spécialités : certaines choses seront très simples à faire avec un langage et se transformeront en calvaire avec un autre. Du coup, qu'utiliser pour faire quoi ?

#### Je ne sais pas trop...

Vous voulez commencer la programmation mais vous ne savez pas trop pourquoi ? Simple curiosité peut-être ? Ou bien vous voulez un langage qui permette de faire un peu de tout, peut-être.

Dans tous ces cas, le langage **Python** est votre meilleur choix. Ce langage n'est pas *le* meilleur dans un domaine en particulier, mais il a l'immense avantage d'être bon partout. Il permet assez facilement de faire des petits programmes, des interfaces graphiques, des sites web, des choses beaucoup plus complexes (intelligence artificielle...). Évidemment le niveau requis pour faire ces choses varie, mais le langage en dessous reste le même.

L'autre avantage de Python est qu'il est simple à apréhender quand on est débutant, tout en en ayant sous le capôt : on peut toujours aller plus loin et utiliser des structures complexes et évoluées avec.

C'est un bon choix dans l'absolu pour débuter en programmation.

<div class="text-center"><a href="https://zestedesavoir.com/tutoriels/799/apprendre-a-programmer-avec-python-3/" class="btn btn-primary">Apprendre à programmer avec Python 3<br /><em>sur Zeste de Savoir</em></a></div>


#### J'aimerais faire du web !

##### Python

Python peut servir à faire des sites web, avec des outils comme [Django](https://zestedesavoir.com/tutoriels/598/developpez-votre-site-web-avec-le-framework-django/), pour des projets moyen-gros, ou [Flask](http://flask.pocoo.org/), un cadre tout léger pour faire des sites plus petits. Par exemple, [Zeste de Savoir](https://zestedesavoir.com/), site que je mentionne régulièrement, est codé avec Django en Python.

##### PHP

Il y a cependant un autre langage pour faire du Web, qu'est lui de base conçu pour : PHP. Assez simple à comprendre dés le début, il a l'avantage d'être très largement disponible chez l'écrasante majorité des hébergeurs[^heberg], permettant ainsi de s'en servir concrètement très facilement.

PHP est un langage tourné vers le web. Seul, il permet déjà de faire des sites sympas ; pour cela, il suffit d'apprendre le langage en lui-même (logique). Ça tombe bien, il y a des cours pour ça, en l'occurrence sur *OpenClassrooms*[^oc] (il n'y en a malheureusement pas sur Zeste de Savoir... du moins pas encore).

<div class="text-center"><a href="https://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql" class="btn btn-primary">Concevez votre site web avec PHP et MySQL<br /><em>sur OpenClassrooms</em></a></div>

##### ...avec Symfony, pour des gros projets

Si vous voulez aller plus loin sur le web, il existe des outils plus gros, plus rigoureux et moins facile d'utilisation aussi, mais qui structurent bien le code (on parle de *framework*).  
Pour PHP, la référence absolue est [Symfony](https://symfony.com/), actuellement dans sa version 3. C'est un *framework* français très populaire parmis les grand sites (ou même certains petits). Si vous n'avez pas peur de l'anglais[^anglais], un tutoriel est proposé par les auteurs de Symfony pour la dernière version (3.1 à l'heure où j'écris ces lignes).

<div class="text-center"><a href="https://symfony.com/doc/current/book/index.html" class="btn btn-primary">The Symfony Book<br /><em>par les auteurs de Symfony</em></a></div>

Sinon, Zeste de Savoir propose [un tutoriel assez complet](https://zestedesavoir.com/tutoriels/620/developpez-votre-site-web-avec-le-framework-symfony2/) mais destiné à une ancienne version de Symfony (2). Il peut servir de base, même s'il faudra vous renseigner sur les changements.

##### ...avec Silex, pour des plus petits projets bien structurés

Le problème de Symfony c'est que c'est gros, un peu trop pour des petits projets pour lesquels Symfony serait vraiment *trop*.  
Pour ces cas il existe ce que l'on appelle des *micro-framework*. [Silex](http://silex.sensiolabs.org/) est l'un d'entre eux que j'affectionne tout particulièrement — d'ailleurs ce site est réalisé avec Silex ([voir la source](https://github.com/AmauryCarrade/Website)). Un guide existe par les créateurs de Silex[^silex_authors] en anglais, si vous n'avez pas peur de cette langue.

<div class="text-center"><a href="http://silex.sensiolabs.org/doc/master/intro.html" class="btn btn-primary">The Silex Book<br /><em>par les auteurs de Silex</em></a></div>

[^heberg]: Un hébergeur est une entreprise, comme [OVH](https://ovh.net) ou [Online](https://online.net/), ou une association, qui offre ou vend la mise en ligne de sites web. Tous les sites internet passent par un hébergeur pour être accessible sur internet.
[^oc]:
    OpenClassrooms, ex-Site du Zéro, est un site qui propose des cours depuis déjà longtemps. Cela dit, depuis quelques temps, le site cherche à tenter de forcer l'inscription et/ou le paiement pour les cours. Ne vous y méprenez pas ! Ceux que je lis sont en accès gratuit (sauf vidéos, mais on s'en passe très bien).

    Par contre OC force l'inscription pour consulter plus de trois pages de cours. C'est gratuit et rapide, un peu énervant de se voir forcer la main, je l'admet, mais je ne connais pas d'autre cours qui traite bien du domaine... Si vous en connaissez, n'hésitez pas à me le dire via le formulaire de contact !
[^anglais]: Au passage et honnêtement, si vous avez du mal avec l'anglais, entraînez-vous : la grosse majorité des ressources en informatique sont en anglais.
[^silex_authors]: Les même que Symfony, pour info !


#### Je veux développer pour Minecraft

Minecraft est développé en Java, de même que les plugins pour Bukkit/Spigot et les mods pour Forge. Java est un langage de programmation très connu et très utilisé, mais plus compliqué à apprendre et plus strict. Comme premier langage, la courbe d'apprentissage est un peu plus pentue (même si ça reste faisable).

Zeste de Savoir propose un cours de Java pour débutants. Pour Bukkit/Spigot, il vous faudra plus vous débrouiller : si on trouve quelques tutoriels sur internet (ça se trouve facilement, je vous laisse chercher), l'essentiel de ce qu'on apprend se fait directement en lisant la JavaDoc des projets.

<div class="text-center"><a href="https://zestedesavoir.com/tutoriels/646/apprenez-a-programmer-en-java/" class="btn btn-primary">Apprenez à programmer en Java<br /><em>sur Zeste de Savoir</em></a></div>

Au passage, Java est également le langage principal pour [développer des applications pour Android](https://zestedesavoir.com/tutoriels/624/creez-des-applications-pour-android/), si ce sujet vous intéresse :) .

Bonne chance !
