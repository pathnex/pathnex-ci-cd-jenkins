
output "instance_public_ips" {
  value = {
    for k, instance in aws_instance.pathnex :
    k => {
      public_ip = instance.public_ip
      private_ip = instance.private_ip
  }
}
}