resource "aws_instance" "pathnex" {
  for_each = local.instances

  ami                         = data.aws_ami.amazon_linux.id
  instance_type               = each.value.instance_type
  associate_public_ip_address = true
  key_name                    = var.key_name

  subnet_id = data.aws_subnets.default.ids[0]

  vpc_security_group_ids = [
    data.aws_security_group.default.id
  ]

  tags = {
    Name = "pathnex-${each.key}"
  }
}