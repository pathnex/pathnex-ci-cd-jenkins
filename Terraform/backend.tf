terraform {
  backend "s3" {
    bucket         = "pathnex-devops-terraform"
    key            = "batch/may/ansible/terraform.tfstate"
    region         = "ap-south-1"
    dynamodb_table = "terraform-locks" # optional but recommended
    encrypt        = true
  }
}
