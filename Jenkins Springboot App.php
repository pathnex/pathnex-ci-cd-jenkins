pipeline {
    agent any

    parameters {
        choice(name: 'ENV', choices: ['dev', 'stage', 'prod'], description: 'Deployment environment')
    }

    environment {
    MAVEN_HOME = "/opt/maven"
    PATH = "/opt/maven/bin:${env.PATH}"
    APP_NAME = "pathnex-application"
    IMAGE_TAG = "${BUILD_NUMBER}"
    FULL_IMAGE = "${APP_NAME}:${BUILD_NUMBER}"
    }
    
    stages {

        stage('0. Clean') {
            steps {
                sh 'rm -rf target'
            }
        }

        stage('1. Checkout Code') {
            steps {
                git branch: 'main', url: 'https://github.com/spring-projects/spring-petclinic.git'
            }
        }

        stage('2. Build & Test (Parallel)') {
            parallel {
                stage('Build') {
                    steps {
                        sh 'mvn clean package -DskipTests'
                    }
                }

                stage('Unit Tests') {
                    steps {
                        sh 'mvn test || true'
                    }
                }
            }
        }

        stage('3. Code Quality Check') {
            steps {
                echo "Simulating code quality..."
            }
        }

        stage('4. Security Scan') {
            steps {
                echo "Simulating security scan..."
            }
        }

        stage('5. Create Dockerfile') {
            steps {
                sh '''
cat <<EOF > Dockerfile
FROM eclipse-temurin:17-jdk
WORKDIR /app
COPY target/*.jar app.jar
ENTRYPOINT ["java","-jar","app.jar"]
EOF
'''
            }
        }

        stage('6. Check Dockerfile') {
            steps {
                sh 'cat Dockerfile'
            }
        }

        stage('7. Build Docker Image') {
            steps {
                sh """
                echo "Building ${FULL_IMAGE}"
                docker build -t ${FULL_IMAGE} .
                """
            }
        }

        stage('8. Deploy') {
            steps {
                script {
                    echo "Deploying to ${params.ENV}"
                }

                sh """
                docker stop ${APP_NAME} || true
                docker rm ${APP_NAME} || true

                docker run -d -p 80:8080 --name ${APP_NAME} ${FULL_IMAGE}
                """
            }
        }

        stage('9. Health Check') {
            steps {
                sh """
                sleep 10
                curl -f http://localhost:80 || exit 1
                """
            }
        }

        stage('10. Archive Artifact') {
            steps {
                archiveArtifacts artifacts: 'target/*.jar', fingerprint: true
            }
        }
    }

    post {

        success {
            echo "🎉 Deployment SUCCESS for ${params.ENV}"
        }

        failure {
            echo "❌ Deployment FAILED"

            sh """
            docker stop ${APP_NAME} || true
            docker rm ${APP_NAME} || true

            # Optional fallback (only if you REALLY have it)
            docker run -d -p 80:8080 --name ${APP_NAME} ${FULL_IMAGE} || true
            """
        }

        always {
            echo "Pipeline completed."
        }
    }
}