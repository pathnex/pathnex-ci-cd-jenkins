locals {
  instances = {
    ec2-1 = {
      instance_type = "m7i-flex.large"
    }

    ec2-2 = {
      instance_type = "t3.micro"
    }
  }
}