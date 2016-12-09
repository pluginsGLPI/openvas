# OpenVAS GLPi plugin for GLPi

This plugin integrates [OpenVAS](https://www.openvas.org/) with GLPI.

## Features

* Retrieve OpenVAS informations for GLPi assets
* Get severity & threat for assets
* Import vulnerabities (results)
* Create tickets based on vulnerabities data
* Create tasks, request start and stop

## Installation

### Prerequisites

The plugin has only been tested on Linux.

You need the followings components:
- GLPI (at least 9.1.1 version)
- OpenVAS manager up and running
- PHP >= 5.5
- openvas-cli package installed to provide omp executable

### OpenVAS configuration

You need to create a user able to connect and administrate OpenVAS.
The use should be able to see all data, and create tasks/request task start & stop  

### Glpi Configuration

Copy the plugin folder into your glpi/plugins directory.
The folder must be named 'prelude' otherwise GLPI framework will fail to initialize the plugin.

Next, go to glpi interface, navigate to the _Administatrion > Plugins_ page.
Find the prelude line, click on _Install_ button, and after refresh, click on _Enable_ button.

Once the plugin enabled, you may click on this name or go into _Setup > General_ menu to display the plugin configuration.

You will see this page:

![Prelude plugin empty configuration](https://raw.githubusercontent.com/pluginsGLPI/openvas/develop/screenshots/config.png)

Fill the configuration fields in order to perform the OpenVAS connection :

* Host: IP or hostname of OpenVAS manager
* Manager port: OpenVAS management port (by default 9390)
* Console port: OpenVAS management console port (by default 9392)
* User: user to connect to OpenVAS
* Password: user's password
* Target retention delay: number of days after which target & vulnerabities infos are deleted
* Number of days for searches: value to use for time restriction during OpenVAS queries
* Request source: the request source to use when creating tickets from vulnerabities
* Color palette: colors to represent OpenVAS threat levels

The test button is used to test the connection to OpenVAS manager.

## Usage

### Automatic actions

3 automatic actions are added by the plugin:

* openvasSynchronize: used to perform hosts to asset matching and informations retrieval
* openvasSynchronizeVulnerabilities: import vulnerabities (results) from OpenVAS in GLPi. Can also create tickets
* openvasClean : clean not accurate data, based on the creation date

You must start by launching openvasSynchronize, then openvasSynchronizeVulnerabilities.

### OpenVAS vulnerabities menu

Vulnerabities can be found at  _Tools > OpenVAS_ menu.
A small green icon allows user to switch from vulnerabities view to tasks view.

![vulnerabity display](https://raw.githubusercontent.com/pluginsGLPI/openvas/develop/screenshots/vulnerability.png)

A vulnerabity can be linked to several hosts.
If a host is linked to an asset in GLPi, you can access the host directly from the vulnerabity.

### OpenVAS asset tab

If a GLPi asset is linked to an OpenVAS host, a new _OpenVAS_ tab is diplayed.
The tab lists:

* general informations (name, comment, severity & threat)
* the list of tasks for this host
* vulnerabities linked to the host

### OpenVAS vulnerabities rules

A rules engine is available at _Administration > Rules_.
It has 2 actions :

* ignore vulnerabity import
* create a ticket based on a template

### Ticket creation from a vulnerabity


## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer
