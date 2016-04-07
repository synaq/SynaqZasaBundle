# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network :private_network, ip: "192.168.255.4"
  config.vm.synced_folder ".", "/vagrant", type: "nfs"

    config.vm.provision "puppet" do |puppet|
      puppet.manifests_path = "manifests"
      puppet.manifest_file  = "site.pp"
    end
end
