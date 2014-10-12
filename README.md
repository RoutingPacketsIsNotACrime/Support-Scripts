Support Scripts
================
Various support scripts for the backend services

/Shard/
-----
+ `updateBlocked.sh` - Grabs the latest blocked.org.uk data, unzips it and imports it into memcache for the /view/ endpoint
+ `blockedBlockedMemcache.php` - Called by updateBlocked.sh to do the memcache import

/Squid/
----- 
+ `prefixes` - A list of known subnets from UK ISPs that perform filtering _(Feedback and additions welcome!)_
+ `updateDomainACL.sh` - Pulls down the latest FQDN whitelist from the central PacketFlagon node for loading into Squid

/DeadHand/
--------
From Wikipedia: 
> Dead Hand (Russian: Система «Периметр», Systema "Perimetr", 15Э601), known also as Perimeter, is a Cold-War-era nuclear-control system used by the Soviet Union and is an example of fail-deadly deterrence, it can automatically trigger the launch of the Russian intercontinental ballistic missiles (ICBMs) if a nuclear strike is detected.

The PacketFlagon DeadHand has access to a set of accounts for domain registration, virtual machine hosting and twitter accounts with a **lot** of pre-paid credit. In normal operation it simply checks to see if the last domain registered has been blocked by the ISPs, if it detects that it has then it registers a new domain, spins up a new VM, bootstraps it into Chef *(which configures the DNS, hosting and apache configuration to host a PacketFlagon ProxyShard on the new domain)* and then tweets out the new domain so people can use it.

There is a secondary system that isn't yet open sourced; in the event that the author doesn't checkin within 36 hours, if certain servers fail their HMAC handshake, or if certain domains fail to respond with a HMAC'd TXT record the system will respond by shifting **everything** to new domains and servers registered by an account the author doesn't have access to. All server passwords are rotated, SSH keys are purged, SSHd is shutdown and the LUKs key in slot0 is destroyed. The system will be totally autonomous with access to enough pre-paid credit to operate for 5 years under expected operation.
