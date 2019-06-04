# ione-whmcs-module
IONe based Provisioning Module for WHMCS

Manage and account your Virtual Infrastructure using IOpenNebula and WHMCS

# Installation

Before you start, don't forget to install and configure IONe Cloud at your OpenNebula. [Manual here](https://github.com/ione-cloud/ione-sunstone).

## 1. WHMCS Pre-configure

### Add IONe host to WHMCS API WhiteList
The path is: 

    Setup -> 
      General Settings ->
        Security ->
          API IP Access Restriction

### Add Product Group
Path:

    Setup ->
      Products/Services ->
        Create a New Group

Then, add a **New Product**, for example 'Small VM'.

> **Important!**
> Don't forget to fill **Product Description** field

Product Description Example:
```json
{
    "properties": [
        {
            "GROUP": "cpu_core",
            "VALUE": "1",
            "TITLE": "1"
        },
        {
            "GROUP": "ram",
            "VALUE": "1 Gb",
            "TITLE": "1 Gb"
        },
        {
            "GROUP": "hdd",
            "VALUE": "30 Gb",
            "TITLE": "30 Gb",
            "IOPS": "500"
        }
    ]
}
```

### Add Product Addon
Path:

    Setup ->
      Products/Services ->
        Products Addon

> **Important!**
> Don'f forget to fill **Product Description** field

Product Description Example:
```json
{
    "GROUP": "os",
    "TITLE": "CentOS x64",
    "VALUE": "1"
}
```
Check the **Show on Order** checkbox.

This is the minimal settings for VMs auto-install from order.

## 2. Install and configure OpenNebula Control Module

Download and extract module from our [github repo](https://github.com/ione-cloud/ione-whmcs-module).

Put *addons* and *servers* to /modules/, so you should get:

    /modules/servers/onconnector/
    /modules/addons/oncontrol/

### Turn on the module
Path:

    Setup ->
      Addon Modules

Press 'Activate' button near 'Open Nebula Control'.

> Important!
> Set Module rights to 'Full Administrator'

### Suspend immunity

If you wish to some of your users have Suspend immunity

1. Open Setup -> Custom Client Fields
2. Add Custom 'Drop Down' Field with the next options: Not Set, Yes, No

### Add Server
Path:

    Setup ->
      Products/Services ->
        Servers ->
          Add New Server

Fill Name and IP Address.
Example:
    Name: IONe Cloud
    IP Address: yourcloud.yourdomain.org

### Install modules

CentOS:
```bash
yum install -y git make automake gcc gcc-c++ kernel-devel ruby-devel zeromq zeromq-devel php-zmq
service httpd restart
```

Ubuntu:
```bash
apt-get install -y git make automake gcc gcc-c++ kernel-devel ruby-devel zeromq zeromq-devel php-zmq
service httpd restart
```

### Set Variables

Open **Addons** -> Open Nebula Control -> **Configuration** -> Configure module
Fill next fields:
* WHMCS admin username - Admin User which will be used for executing some functions. You may create 'oneadmin' user with Administrator rights, for example.
* OpenNebula URL - OpenNebula Front-end(e.g. Sunstone) URL
* IP - IP Address of the host, where IONe Cloud is
* Port - Port, which IONe Cloud listens
* Immunity - Field Name, which you've created before

### Add Template

Open **Addons** -> Open Nebula Control -> **Configuration** -> Configure templates -> **Add**

Enter Template ID from OpenNebula(you may see them by executing this at OpenNebula host: ```onetemplate list```)
Connect it with OS Addon.
Also you may specify some description, if you wish.

## 3. Test Order

1. Add new Order with configured Product
2. Accept this Order
3. The VM installation should start.
4. If not, you may check the Logs at Utilities -> Logs -> Module Log