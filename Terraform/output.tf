
output "instance_public_ips" {
  value = {
    for k, instance in aws_instance.pathnex :
    k => instance.public_ip
  }
}