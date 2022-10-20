pipeline {
    agent {
        label 'jenkins-slave1'
    }
    
    stages {
        stage('Build shopware and start test') {
                // when {
                //     expression { BRANCH_NAME ==~ /(PR.*)/ }
                // }
                steps { 
                    sh script: "docker-compose up -d", label: 'Start shopware'
                    sh script: "docker-compose exec -T -u www-data shopware ./run-test.sh", label: 'Start tests'
                    sh script: "docker-compose down", label: 'Stop shopware'
                }
        }
    }
    // post {
    //     cleanup {
    //         cleanWs deleteDirs: true
    //     }
    // }
}