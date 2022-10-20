pipeline {
    agent {
        label 'jenkins-slave1'
    }
    
    stages {
        stage('Build shopware') {
                // when {
                //     expression { BRANCH_NAME ==~ /(PR.*)/ }
                // }
                steps { 
                    sh script: "docker-compose up -d", label: 'Start shopware'
                    sh script: "docker-compose exec -u www-data shopware ./run-test.sh", label: 'Start tests'
                }
        }
    }
    post {
        cleanup {
            cleanWs deleteDirs: true
        }
    }
}