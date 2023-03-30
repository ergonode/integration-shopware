pipeline {
    agent {
        label 'Slave-1-VMware'
    }
    
    stages {
        stage('Build shopware and start test') {
            when {
                expression { BRANCH_NAME ==~ /(MR.*)/ }
            }
            steps {
                sh script: "docker-compose up -d --build", label: 'Build and start Shopware'
                sh script: "docker-compose exec -T -u www-data shopware ./run-test.sh", label: 'Start tests'
            }
        }
    }
    post {
        cleanup {
            sh script: "docker-compose down", label: 'Stop and remove Shopware'
            cleanWs deleteDirs: true
        }
    }
}