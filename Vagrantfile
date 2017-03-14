# -*- mode: ruby -*-
# vi: set ft=ruby :

# Specify a custom Vagrant box for the demo
DEMO_BOX_NAME = ENV['DEMO_BOX_NAME'] || "debian/jessie64"

# Vagrantfile API/syntax version.
# NB: Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = DEMO_BOX_NAME
  config.vm.synced_folder '.', '/srv/share', id: 'vagrant-share', :nfs => true
  config.vm.synced_folder '.', '/vagrant', disabled: true

  config.vm.provision :ansible do |ansible|
    ansible.limit             = 'node'
    ansible.playbook          = 'ansible/site.yml'
    ansible.inventory_path    = 'ansible/inventory/devbox/hosts'
  end

  config.vm.define "n1" do |n1|
      n1.vm.hostname = "n1"
      n1.vm.network "private_network", ip: "172.20.20.10"
  end

  config.vm.define "n2" do |n2|
      n2.vm.hostname = "n2"
      n2.vm.network "private_network", ip: "172.20.20.11"
  end
end
