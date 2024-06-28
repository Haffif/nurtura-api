import numpy as np

# Generating a right skewed distribution dataset for testing load balancing
np.random.seed(42)  # For reproducibility
data = np.random.gamma(shape=2, scale=2, size=1000)  # Gamma distribution, often right skewed

# Saving the generated dataset to a txt file
np.savetxt('right_skewed_distribution.txt', data, fmt='%.4f')

# Displaying some of the generated data for verification
data[:10]
