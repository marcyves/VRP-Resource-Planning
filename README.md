# VRP
## VRP Resource Planning
Project planification and budgeting
<a name="readme-top"></a>

<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->
[![Issues][issues-shield]][issues-url]
[![GPL 3][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]



<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/marcyves/VRP">
    <img src="public/logo-XDM.png" alt="Logo" width="80" height="80">
  </a>

  <h3 align="center">VRP</h3>

  <p align="center">
  VRP started as a personal side projet in order to help me plan and budget my different projects. Gradually, this is evolving into an ERP System for freelancers and vacataires.
    <br />
    <a href="https://github.com/marcyves/VRP"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/marcyves/VRP">View Demo</a>
    ·
    <a href="https://github.com/marcyves/VRP/issues">Report Bug</a>
    ·
    <a href="https://github.com/marcyves/VRP/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

[![Planary][planary-screenshot]](https://raw.githubusercontent.com/marcyves/plarany/main/_doc/screencapture.png)



<p align="right">(<a href="#readme-top">back to top</a>)</p>



### Built With

This section list any major frameworks/libraries used to bootstrap this project.

* [![Laravel][Laravel.com]][Laravel-url]
* [![Tailwind][Tailwind.com]][Tailwind-url]

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- GETTING STARTED -->
## Getting Started

These are the instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

If you want to run Planary you need to have a MySQL server available, npm and composer installed on your computer.

### Installation

_You need `composer`` to install the project and then follow the usual steps for a Laravel project._

1. Clone the repo
   ```sh
   git clone https://github.com/marcyves/VRP.git
   ```
2. Install Laravel modules
  ```sh
  composer install
  ```
3. Copy .env.example to .env
  Review Data Base parameters, you can go for SQLite or MySQL but in this case you need to set up and start a MySQL server.
4. Set the application key
  ```sh
  php artisan key:generate
  ```
5. Create the Database
  ```sh
  php artisan migrate
  ```
6. Install NPM packages
   ```sh
   npm install
   ```
7. Run Vite
  ```sh
  npm run dev
  ```

<p align="right">(<a href="#readme-top">back to top</a>)</p>




<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ROADMAP -->
## Roadmap

- [x] Create Readme
- [ ] Add "Roadmap" to the readme
- [ ] Multi-language Support
    - [ ] French
    - [ ] Italian

See the [open issues](https://github.com/marcyves/plarany/issues) for a full list of proposed features (and known issues).

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Very Short Documentation

### User Statuses
  * 1 admin (for their company)
  * 2 éditeur
  * 3 rédacteur
  * 4 SuperAdmin (all companies)

## Online demo

You can give a try to the project at 
http://vrp.xdm-consulting.fr/

email: user@xdm.fr
password: topsecret

<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the GPL 3 License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Marc Augier - [@marcyves](https://twitter.com/marcyves)

Project Link: [https://github.com/users/marcyves/projects/3](https://github.com/users/marcyves/projects/3)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

Resources I find helpful and would like to give credit to.

* [Best README Template](https://github.com/marcyves/VRP)
* [Choose an Open Source License](https://choosealicense.com)
* [GitHub Emoji Cheat Sheet](https://www.webpagefx.com/tools/emoji-cheat-sheet)
* [Malven's Flexbox Cheatsheet](https://flexbox.malven.co/)
* [Malven's Grid Cheatsheet](https://grid.malven.co/)
* [Img Shields](https://shields.io)
* [GitHub Pages](https://pages.github.com)
* [Font Awesome](https://fontawesome.com)
* [React Icons](https://react-icons.github.io/react-icons/search)
* [Tailwind documentation](https://tailwindcss.com/docs/installation)
* [Flowbite](https://flowbite.com/)
* [Awesome Badges](https://dev.to/envoy_/150-badges-for-github-pnk)


<p align="right">(<a href="#readme-top">back to top</a>)</p>

## vous avez aimé ?
Pourquoi pas me remercier en m'offrant un café ?

<a href="https://www.buymeacoffee.com/marcyves" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-blue.png" alt="Buy Me A Coffee" width="210" ></a>

Réalisé par [@marcyves](https://github.com/marcyves)

## Notice



<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/othneildrew/Best-README-Template.svg?style=for-the-badge
[contributors-url]: https://github.com/marcyves/VRP/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/othneildrew/Best-README-Template.svg?style=for-the-badge
[forks-url]: https://github.com/marcyves/VRP/network/members
[stars-shield]: https://img.shields.io/github/stars/othneildrew/Best-README-Template.svg?style=for-the-badge
[stars-url]: https://github.com/marcyves/VRP/stargazers
[issues-shield]: https://img.shields.io/github/issues/othneildrew/Best-README-Template.svg?style=for-the-badge
[issues-url]: https://github.com/marcyves/VRP/issues
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/marcyves/VRP/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/marcaugier
[planary-screenshot]: https://raw.githubusercontent.com/marcyves/plarany/main/_doc/screencapture.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 
[Tailwind-url]: https://tailwindui.com/
[Tailwind.com]: https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white
